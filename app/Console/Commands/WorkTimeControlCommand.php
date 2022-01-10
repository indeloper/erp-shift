<?php

namespace App\Console\Commands;

use App\Models\{Group, Project, Task, User};
use App\Traits\{NotificationGenerator, TimeCalculator};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WorkTimeControlCommand extends Command
{
    use TimeCalculator, NotificationGenerator;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work-time:control
        {time=17:00 : Argument for command execution time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command can generate an work time control tasks (at 5 PM) and make tasks expire notification (at 9 PM).';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time = $this->argument('time');

        switch ($time) {
            case '17:00':
                $this->doEveningRoutine();
                break;
            case '21:00':
                $this->doNightRoutine();
                break;
            // Code block below are commented out because of requirements change
            /*case '17:00':
                $this->doEveningRoutine();
                break;*/
        }
    }

    private function doEveningRoutine()
    {
        DB::beginTransaction();

        $projectsWithoutTimeResponsibleUser = Project::contractsStarted()->whereNull('time_responsible_user_id')->get();
        $projectsWithTimeResponsibleUser = Project::contractsStarted()->whereNotNull('time_responsible_user_id')->get();

        foreach ($projectsWithoutTimeResponsibleUser as $projectWithoutTimeResponsibleUser) {
            // Find RPs and send tasks to them
            $projectRPs = $projectWithoutTimeResponsibleUser->respUsers()->whereIn('role', [5, 6])->get();
            // If here no RPs, send task to Main Engineer
            if ($projectRPs->isEmpty()) {
                // Generate task for Main Engineer
                $mainEngineer = Group::find(8)->getUsers()->first();
                $this->createTimeResponsibleUserAssignmentTaskFor($mainEngineer, $projectWithoutTimeResponsibleUser);
            } else {
                foreach ($projectRPs as $projectRP) {
                    $this->createTimeResponsibleUserAssignmentTaskFor($projectRP->user, $projectWithoutTimeResponsibleUser);
                }
            }
        }

        foreach ($projectsWithTimeResponsibleUser as $projectWithTimeResponsibleUser) {
            $this->createWorkTimeControlTaskFor($projectWithTimeResponsibleUser->timeResponsible, $projectWithTimeResponsibleUser);
        }

        DB::commit();
    }

    private function doNightRoutine()
    {
        DB::beginTransaction();

        $today = now()->format('Y-m-d');
        $todayTasks = Task::whereStatus(41)->where('is_solved', 0)->where('created_at', 'like', "%{$today}%")->get();

        foreach ($todayTasks as $todayTask) {
            $this->createWorkTimeControlTaskExpireNotificationFor($todayTask);
        }

        DB::commit();
    }

    private function createTimeResponsibleUserAssignmentTaskFor(User $responsibleUser, Project $project)
    {
        $task = $project->tasks()->create([
            'name' => 'Назначение ответственного за учёт времени в проекте',
            'responsible_user_id' => $responsibleUser->id,
            'project_id' => $project->id,
            'status' => 39,
            'expired_at' => $this->addHours(8)
        ]);

        $task->generateProjectTimeResponsibleAssignmentTaskNotification();
    }

    private function createWorkTimeControlTaskFor(User $responsibleUser, Project $project)
    {
        $task = $project->tasks()->create([
            'name' => 'Контроль рабочего времени',
            'responsible_user_id' => $responsibleUser->id,
            'project_id' => $project->id,
            'status' => 41,
            'expired_at' => $this->addHours(4)
        ]);

        $task->generateWorkTimeControlTaskNotification();
    }
}

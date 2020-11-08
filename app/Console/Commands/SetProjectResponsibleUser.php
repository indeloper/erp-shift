<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;

class SetProjectResponsibleUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:responsible_user 
    {role_id : role of new resp user}
    {user_id : id of new resp user}
    {mode=1 : 1 for replacing and adding where empty, 2 for adding everywhere, 3 for adding where empty } ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change or add resp user to all projects. 
    WRITE ARGUMENTS WITHOUT COMMAS';

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

        $role_id = $this->argument('role_id');
        $new_resp_user = User::find($this->argument('user_id'));
        $mode = $this->argument('mode');


        if ($mode === '1') {
            $resps_to_delete = Project::with('respUsers')->whereHas('respUsers', function ($q) use($role_id) {
                return $q->where('role', $role_id);
            })->count();

            $this->info('Количество проектов, в которых ответственный будет заменён первый из всех: ' . $resps_to_delete);
            $this->info('Количество проектов, в которых ответственный будет добавлен: ' . (Project::count() - $resps_to_delete));

            $confirmation = $this->confirm('Продолжить операцию? Старые ответственные будут удалены, вместо них будет назначен(а) ' . $new_resp_user->full_name);

            if ($confirmation) {
                $projects = Project::with('respUsers')->get();
                foreach ($projects as $project) {
                    $user = $project->respUsers->where('role', $role_id)->first();
                    if ($user){
                        $user->user_id = $new_resp_user->id;
                        $user->save();
                    } else {
                        $project->respUsers()->create([
                            'project_id' => $project->id,
                            'user_id' => $new_resp_user->id,
                            'role' => $role_id
                        ]);
                    }
                }

                $this->info('Операция завершена');
                return true;
            } else {
                $this->info('Операция прервана.');
                return false;
            }
        } elseif ($mode === '2') {
            $projects = Project::get();

            $this->info('Количество проектов, в которых будет добавлен новый (или ещё один) ответственный: ' . $projects->count());

            $confirmation = $this->confirm('Вы хотите добавить нового (или ещё одного) ответственного во все проекты?');

            if ($confirmation) {
                foreach ($projects as $project) {
                    $project->respUsers()->create([
                        'project_id' => $project->id,
                        'user_id' => $new_resp_user->id,
                        'role' => $role_id
                    ]);
                }

                $this->info('Операция завершена');
                return true;
            } else {
                $this->info('Операция прервана.');
                return false;
            }
        } elseif ($mode === '3') {
            $proj_to_add_resp = Project::with('respUsers')->whereDoesntHave('respUsers', function ($q) use($role_id, $new_resp_user) {
                return $q->where('role', $role_id)->where('user_id', $new_resp_user->id);
            })->get();

            $this->info('Количество проектов, в которых ответственный будет добавлен: ' . ($proj_to_add_resp->count()));

            $confirmation = $this->confirm('Продолжить операцию? В этих проектах ' . $new_resp_user->role_codes[$role_id] . ' будет назначен(а) ' . $new_resp_user->full_name);

            if ($confirmation) {
                foreach ($proj_to_add_resp as $project) {
                    $project->respUsers()->create([
                        'project_id' => $project->id,
                        'user_id' => $new_resp_user->id,
                        'role' => $role_id
                    ]);
                }

                $this->info('Операция завершена');
                return true;
            } else {
                $this->info('Операция прервана.');
                return false;
            }
        }
    }
}

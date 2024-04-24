<?php

namespace App\Console\Commands;

use App\Domain\Enum\NotificationType;
use App\Models\Contractors\Contractor;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Fomvasss\Dadata\Facades\DadataSuggest;
use Illuminate\Console\Command;

class CheckContractorsInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:contractors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command check contractors info from dadata and create task and show differences';

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
    public function handle(): void
    {
        $contractors = Contractor::query()->get();
        $changes = 0;
        $errors = 0;
        $exists = 0;
        foreach ($contractors as $contractor) {
            if ($contractor->ogrn) {
                try  {
                    $result = DadataSuggest::suggest('party', ["query" => $contractor->ogrn, 'count' => 1]);

                    $change = $this->checkAndCreateTask($result, $contractor);

                    if ($change) {
                        $changes += 1;
                    } else {
                        $exists += 1;
                    }
                } catch (\RuntimeException $e) {
                    $this->error('Error with ' . $contractor->ogrn . ' ogrn!');

                    $errors += 1;
                }
            }
        }

        $this->line($errors . ' errors');
        $this->line($changes . ' changes');
        $this->line($exists . ' exists or empty');
    }

    public function checkAndCreateTask(array $dadata, Contractor $contractor): bool
    {
        $changingFields = [];
        if ($dadata['data']['name']['full_with_opf'] != $contractor->full_name) {
            $changingFields[] = ['field_name' => 'full_name', 'value' => $dadata['data']['name']['full_with_opf'], 'old_value' => $contractor->full_name];
        }
        if ($dadata['data']['name']['short_with_opf'] != $contractor->short_name) {
            $changingFields[] = ['field_name' => 'short_name', 'value' => $dadata['data']['name']['short_with_opf'], 'old_value' => $contractor->short_name];
        }
        if (isset($dadata['data']['inn']) && $dadata['data']['inn'] != $contractor->inn) {
            $changingFields[] = ['field_name' => 'inn', 'value' => $dadata['data']['inn'], 'old_value' => $contractor->inn];
        }
        if (isset($dadata['data']['kpp']) && $dadata['data']['kpp'] != $contractor->kpp) {
            $changingFields[] = ['field_name' => 'kpp', 'value' => $dadata['data']['kpp'], 'old_value' => $contractor->kpp];
        }
        if (isset($dadata['data']['address']['value']) && $dadata['data']['address']['value'] != $contractor->legal_address) {
            $changingFields[] = ['field_name' => 'legal_address', 'value' => $dadata['data']['address']['value'], 'old_value' => $contractor->legal_address];
        }
        if (isset($dadata['data']['management']['name']) && $dadata['data']['management']['name'] != $contractor->general_manager) {
            $changingFields[] = ['field_name' => 'general_manager', 'value' => $dadata['data']['management']['name'], 'old_value' => $contractor->general_manager];
        }

        if (empty($changingFields)) {
            return false;
        }

        $task = Task::where('is_solved', 0)->whereStatus(37)->where('contractor_id', $contractor->id)->first();

        if ($task) {
            return false;
        }

        $task = Task::where('is_solved', 1)->whereResult(2)->whereStatus(37)->where('contractor_id', $contractor->id)->orderBy('id', 'desc')->first();

        if ($task && !Carbon::now()->subDays(30)->gt(Carbon::parse($task->created_at))) {
            return false;
        }

        $users = User::whereIn('group_id', [7])->get();


        foreach ($users as $user) {
            $this->createTask($contractor, $changingFields, $user->id);
        }

        return true;
    }

    public function createTask(Contractor $contractor, array $changingFields, int $responsible_user_id): void
    {
        $task = new Task();
        $task->name = 'Проверка изменений в информации о контрагенте';
        $task->contractor_id = $contractor->id;
        $task->responsible_user_id = $responsible_user_id;
        $task->user_id = 0;
        $task->status = 37;
        $task->expired_at = Carbon::now()->addDay();
        $task->save();
        $task->fresh();

        foreach ($changingFields as $field) {
            $task->changing_fields()->create($field);
        }

        dispatchNotify(
            $task->responsible_user_id,
            'Новая задача «' . $task->name . '»',
            '',
            NotificationType::CONTRACTOR_CHANGES_VERIFICATION_TASK_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                'task_id' => $task->id,
                'contractor_id' => $task->contractor_id,
                'project_id' => 0,
                'object_id' => 0,
            ]
        );
    }
}

<?php

namespace App\Console\Commands;

use App\Events\NotificationCreated;
use App\Http\Controllers\q3wMaterial\operations\q3wMaterialTransferOperationController;
use App\Models\Notification;
use App\Models\Project;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckOverdueMaterialAccountingOperation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'materialOperation:checkOverdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Checks if it's overdue material accounting operation and sends notification to responsibility users who based on operation state";

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
        $notificationStartPeriodDate = Carbon::createFromTime(7,30,00);
        $notificationEndPeriodDate = Carbon::createFromTime(20,00,00);

        if (Carbon::now() > $notificationStartPeriodDate && Carbon::now() < $notificationEndPeriodDate) {
            $overdueTimeInHours = 12;
            DB::beginTransaction();

            $overduedOperations = q3wMaterialOperation::where('updated_at', '<', Carbon::now()->subHours($overdueTimeInHours))
                ->onlyActive()
                ->get();

            foreach ($overduedOperations as $operation) {
                $this->info('Operation #' . $operation->id . ' is overdued');
                $notificationText = 'Активность по операции отсутствует более 12 часов.';

                switch ($operation->operation_route_stage_id) {
                    case 6:
                    case 30:
                        //Получателю
                        $notifiableUser = $operation->destination_responsible_user_id;
                        $projectObjectId = $operation->destination_project_object_id;
                        (new q3wMaterialTransferOperationController)->sendTransferNotification($operation, $notificationText, $notifiableUser, $projectObjectId);
                        break;
                    case 11:
                    case 25:
                        //Отправителю
                        $notifiableUser = $operation->source_responsible_user_id;
                        $projectObjectId = $operation->source_project_object_id;
                        (new q3wMaterialTransferOperationController)->sendTransferNotification($operation, $notificationText, $notifiableUser, $projectObjectId);
                        break;
                    case 19:
                        //Руководителю отправителя
                        $projectObjectId = $operation->source_project_object_id;
                        (new q3wMaterialTransferOperationController)->sendTransferNotificationToResponsibilityUsersOfObject($operation, $notificationText, $projectObjectId);
                        break;
                    case 38:
                        //Руководителю получателя
                        $projectObjectId = $operation->destination_project_object_id;
                        (new q3wMaterialTransferOperationController)->sendTransferNotificationToResponsibilityUsersOfObject($operation, $notificationText, $projectObjectId);
                        break;
                }
            }

            DB::commit();
        }
    }
}

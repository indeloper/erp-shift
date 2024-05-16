<?php

namespace App\Console\Commands;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Notifications\TechnicalMaintence\TechnicalMaintenanceCompletionNotice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendNotificationsNeedContract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:need-contract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $operations = MaterialAccountingOperation::query()
            ->whereNotIn('object_id_to', [76, 192])
            ->whereDate('created_at', '>', Carbon::parse('2020-04-28'))
            ->whereIn('type', [1, 4])
            ->where('contract_id', null)
            ->doesntHave('contractTask')
            ->groupBy('object_id_to')
            ->get();

        foreach ($operations as $operation) {
// TODO заменить хардкод 28
            TechnicalMaintenanceCompletionNotice::send(
                28,
                [
                    'name' => 'На объекте ' . $operation->object_to->name_tag . ' существуют операции без договора!',
                    'object_id' => $operation->object_id_to
                ]
            );
        }
    }
}

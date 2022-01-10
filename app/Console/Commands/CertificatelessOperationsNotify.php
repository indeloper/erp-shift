<?php

namespace App\Console\Commands;

use App\Models\Contract\Contract;
use App\Traits\NotificationGenerator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CertificatelessOperationsNotify extends Command
{
    use NotificationGenerator;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificatless-operations:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command finds material accounting operations without certificates and make notifications for some persons';

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
        /*DB::beginTransaction();

        // Find contracts with operations with ks_date that now in period from ks_date - start_notifying_before up to ks_date
        $contracts = Contract::whereNotNull('ks_date')->has('operations', function($contr_q) {
            $contr_q->where('type', 1);
        })->get()
            ->filter(function ($contract) {
                $now = now();
                $lowerRange = Carbon::createFromFormat('d', $contract->ks_date)->month($now->month)->subDays($contract->start_notifying_before ?? 10);
                $ks_date = Carbon::createFromFormat('d', $contract->ks_date)->month($now->month);
                if ($now->gt($ks_date)) {
                    $lowerRange->addMonth();
                    $ks_date->addMonth();
                }
                return $now->isBetween($lowerRange, $ks_date);
        });
        // Foreach contract
        foreach ($contracts as $contract) {
            // Check operations
            $certificatelessOperationsExist = ($contract->operations()->where('status', '!=' , 7)
                    ->whereHas('materialsPartTo', function($part) {
                        return $part->whereDoesntHave('certificates');
                    }))->count() > 0;
            // If at least one operation have part savings without certificates
            if ($certificatelessOperationsExist) {
                // Make notifications
                $this->generateCertificatelessOperationsNotification($contract);
            }
            // Otherwise - do nothing
        }

        // Fin
        DB::commit();*/
    }
}

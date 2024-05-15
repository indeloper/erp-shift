<?php

namespace App\Console\Commands;

use App\Models\Contract\Contract;
use Illuminate\Console\Command;

class ManualContractsRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:remove {ids : list of contract ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "This command remove contracts with relations. All you need to do is give me a list of contract ids that you want to exterminate. Like this 'php artisan contracts:remove 1,2,3,4,5'";

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
        $ids_list = $this->argument('ids');
        // make array from argument, remove empty values
        $contract_ids = array_diff(explode(',', $ids_list), ['']);
        $deleted = [];

        foreach ($contract_ids as $contract_id) {
            $contract = Contract::find($contract_id);
            if ($contract) {
                $contract->manual_delete();
                $deleted[] = $contract_id;
            } else {
                $problem = $this->confirm("Whoops, I can't find a contract with id ".$contract_id.'. Skip?');

                $problem ?
                    $this->info('Alright, we will try next time with id '.$contract_id.' ;)') :
                    $this->info("Now I can't do more for you, sorry. Check contract with id ".$contract_id);
            }
        }

        return count($deleted) ? $this->info('Nice, contracts removed!') : $this->info('Nothing removed');
    }
}

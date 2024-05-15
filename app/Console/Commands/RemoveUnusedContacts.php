<?php

namespace App\Console\Commands;

use App\Models\Contractors\ContractorContact;
use App\Models\ProjectContact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveUnusedContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unused contacts from contractors and contacts';

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
        DB::beginTransaction();

        ContractorContact::where('contractor_id', 0)->delete();
        ProjectContact::where('project_id', 0)->delete();

        DB::commit();

        $this->info('Contacts cleaned!');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Contractors\Contractor;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckContractorContactsAdding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contractors:check-contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command find contractors without contracts and notify some users about it';

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
        DB::beginTransaction();

        // find fresh (not older than three days) contractors without contacts with creator
        $contactless_contractors = Contractor::where('in_archive', 0)->doesntHave('contacts')->has('creator')
            ->whereDate('created_at', '>=', now()->subDays(3)->toDateTimeString())->get();

        foreach ($contactless_contractors as $contactless_contractor) {
            // find diff in days
            $created = Carbon::parse($contactless_contractor->created_at);
            $diff_in_days = now()->diffInDays($created);

            if ($diff_in_days >= 1) {
                // create notify
                $contactless_contractor->create_notify($diff_in_days);
            }
        }

        DB::commit();

        return $this->info('Contactless contractors checked!');
    }
}

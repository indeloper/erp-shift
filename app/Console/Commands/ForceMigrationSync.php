<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ForceMigrationSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:force_sync {raw_migrations : String with migrations to parse}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force sync migration table with migrations you pass. \n 
    Just put names in format like this:
        |
//| No   | 2019_04_01_114944_create_work_volume_materials_table                                    |       |
//| No   | 2019_04_01_121014_create_work_volume_requests_table                                     |       |
//| No   | 2019_04_01_121305_create_work_volume_works_table                                        |       |';

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
        // DON'T PUSH UNCOMMENTED  lifehack for Windows users, uncomment and put the same migrations directly (
        //        $migrations = array_filter(explode('|',  '| 2019_05_17_161837_change_count_column_places_in_work_volume_materials_table
        //'), function($item) {
        //            return strpos($item, '_');
        //        });

        $migrations = array_filter(explode('|', $this->argument('raw_migrations')), function ($item) {
            return strpos($item, '_');
        });

        $batch = DB::table('migrations')->max('batch') + 1;

        DB::beginTransaction();

        foreach ($migrations as $migration) {
            $migrated = DB::table('migrations')->where('migration', trim($migration))->first();

            if (! $migrated) {
                DB::table('migrations')->insert([
                    'migration' => trim($migration),
                    'batch' => $batch,
                ]);
            }

            $this->info($migration.'was forcibly synced');
        }

        DB::commit();
    }
}

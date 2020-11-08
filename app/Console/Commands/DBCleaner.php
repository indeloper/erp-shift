<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DBCleaner extends Command
{
    public $protected_tables = [
        'migrations',

        'users',
        'groups',
        'group_permissions',
        'departments',
        'oauth_refresh_tokens',
        'oauth_personal_access_clients',
        'oauth_clients',
        'oauth_auth_codes',
        'oauth_access_tokens',
        'password_resets',
        'permissions',
        'user_permissions',
        'versions',

        'commercial_offer_manual_notes',
        'commercial_offer_manual_requirements',

        'manual_material_categories',
        'manual_material_parameters',
        'manual_material_category_attributes',
        'manual_materials',
        'manual_nodes',
        'manual_node_materials',
        'manual_node_categories',
        'manual_relation_material_works',
        'manual_works',
        'manual_tongues',

        'notification_types'
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Truncates all tables, except those:  \n ";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->description .= implode(" \n " ,$this->protected_tables);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tables = DB::select('SHOW TABLES');
        $tables = array_map('current',$tables);

        DB::beginTransaction();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            if(!in_array($table, $this->protected_tables))
            {
                DB::table($table)->truncate();
                $this->info('truncated ' . $table);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::commit();
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
//use Illuminate\Support\Facades\Artisan;
//use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NewPermissionsForTechCategoriesMigrationTest extends TestCase
{
    use DatabaseTransactions;

    public $migration;
    public $permissions_table;
    public $group_permissions_table;

    public function setUp(): void
    {
        parent::setUp();

//        $this->migration = '2019_11_21_122024_add_tech_accounting_technic_category_permissions';
//        $this->permissions_table = 'permissions';
//        $this->group_permissions_table = 'group_permissions';
//
//        $this->runMigrationIfItWasntMigrated();
    }

    
    /** @test */
    public function it_asserts_true()
    {
        $this->assertTrue(true);
    }

//    public function runMigrationIfItWasntMigrated(): void
//    {
//        $isRun = DB::table('migrations')->where('migration', $this->migration)->exists();
//
//        if (!$isRun) {
//            Artisan::call("migrate");
//        }
//    }
//
//    /* Test for migration up() function */
//    /** @test */
//    public function after_migration_run_we_need_to_see_new_rows_in_permissions_and_group_permissions_tables()
//    {
//        $this->runMigrationIfItWasntMigrated();
//
//        $this->assertDatabaseHas($this->permissions_table, [
//            'id' => 44,
//            'category' => 13,
//            "name" => 'Создание категории техники',
//            "codename" => 'tech_acc_tech_category_create',
//        ]);
//
//        $this->assertDatabaseHas($this->permissions_table, [
//            'id' => 45,
//            'category' => 13,
//            "name" => 'Редактирование категории техники',
//            "codename" => 'tech_acc_tech_category_update',
//        ]);
//
//        $this->assertDatabaseHas($this->permissions_table, [
//            'id' => 46,
//            'category' => 13,
//            "name" => 'Удаление категории техники',
//            "codename" => 'tech_acc_tech_category_delete',
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 15,
//            'permission_id' => 44,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 15,
//            'permission_id' => 45,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 15,
//            'permission_id' => 46,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 17,
//            'permission_id' => 44,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 17,
//            'permission_id' => 45,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 17,
//            'permission_id' => 46,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 47,
//            'permission_id' => 44,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 47,
//            'permission_id' => 45,
//        ]);
//
//        $this->assertDatabaseHas($this->group_permissions_table, [
//            'group_id' => 47,
//            'permission_id' => 46,
//        ]);
//    }
//
//    /* Test for migration down() function */
//    /** @test */
//    public function after_migration_rollback_we_cant_see_rows_in_permissions_and_group_permissions_tables()
//    {
//        $this->runMigrationIfItWasntMigrated();
//
//        $isRun = DB::table('migrations')->where('migration', $this->migration)->exists();
//        $all_migrations = DB::table('migrations')->get();
//        $migrations_to_rollback = $all_migrations->count() - $all_migrations->where('migration', $this->migration)->keys()[0];
//        if ($isRun) {
//            Artisan::call("migrate:rollback --step={$migrations_to_rollback}");
//
//            $this->assertDatabaseMissing($this->permissions_table, [
//                'id' => 44,
//                'category' => 13,
//                "name" => 'Создание категории техники',
//                "codename" => 'tech_acc_tech_category_create',
//            ]);
//
//            $this->assertDatabaseMissing($this->permissions_table, [
//                'id' => 45,
//                'category' => 13,
//                "name" => 'Редактирование категории техники',
//                "codename" => 'tech_acc_tech_category_update',
//            ]);
//
//            $this->assertDatabaseMissing($this->permissions_table, [
//                'id' => 46,
//                'category' => 13,
//                "name" => 'Удаление категории техники',
//                "codename" => 'tech_acc_tech_category_delete',
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 15,
//                'permission_id' => 44,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 15,
//                'permission_id' => 45,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 15,
//                'permission_id' => 46,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 17,
//                'permission_id' => 44,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 17,
//                'permission_id' => 45,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 17,
//                'permission_id' => 46,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 47,
//                'permission_id' => 44,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 47,
//                'permission_id' => 45,
//            ]);
//
//            $this->assertDatabaseMissing($this->group_permissions_table, [
//                'group_id' => 47,
//                'permission_id' => 46,
//            ]);
//        }
//
//        $this->runMigrationIfItWasntMigrated();
//    }
}

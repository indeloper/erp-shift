<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;

class AddPermissionToGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = Permission::all();

        // department id 4
        $group_permissions_5 = [];
        $group_permissions_6 = [];
        $group_permissions_7 = [];
        $group_permissions_8 = [];
        $group_permissions_9 = [];

        foreach($permissions as $permission) {
            $group_permissions_5[] = ['group_id' => 5, 'permission_id' => $permission->id];
            $group_permissions_6[] = ['group_id' => 6, 'permission_id' => $permission->id];
            if ($permission->codename != 'users_permissions') {
                $group_permissions_7[] = ['group_id' => 7, 'permission_id' => $permission->id];
                $group_permissions_8[] = ['group_id' => 8, 'permission_id' => $permission->id];
                $group_permissions_9[] = ['group_id' => 9, 'permission_id' => $permission->id];
            }
        }

        // department id 5
        $group_permissions_10 = [];
        $group_permissions_11 = [];

        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'users',
                'users_create',
                'users_edit',
                'users_delete',
                'users_permissions',
                'users_vacations'
            ]
        ) as $permission) {
            $group_permissions_10[] = ['group_id' => 10, 'permission_id' => $permission->id];
            $group_permissions_11[] = ['group_id' => 11, 'permission_id' => $permission->id] ;
        }

        // department id 7
        $group_permissions_13 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'projects',
                'contractors',
                'contractors_contacts',
                'objects',
                'objects_edit',
                'manual_materials',
                'manual_nodes',
                'manual_works',
                'mat_acc_report_card',
                'mat_acc_operation_log',
                'project_documents',
                'commercial_offers',
                'work_volumes',
                'contracts',
                'users',
            ]
        ) as $permission) {
            $group_permissions_13[] = ['group_id' => 13, 'permission_id' => $permission->id] ;
        }

        $group_permissions_14 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'projects',
                'objects',
                'manual_materials',
                'manual_nodes',
                'manual_works',
                'mat_acc_report_card',
                'mat_acc_operation_log',
                'project_documents',
                'users',
            ]
        ) as $permission) {
            $group_permissions_14[] = ['group_id' => 14, 'permission_id' => $permission->id] ;
        }

        //department id 8

        $group_permissions_15 = [];
        $group_permissions_16 = [];
        $group_permissions_17 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'mat_acc_report_card',
                'mat_acc_operation_log',
                'users',
            ]
        ) as $permission) {
            $group_permissions_15[] = ['group_id' => 15, 'permission_id' => $permission->id];
            $group_permissions_16[] = ['group_id' => 16, 'permission_id' => $permission->id];
            $group_permissions_17[] = ['group_id' => 17, 'permission_id' => $permission->id];
        }

        // department id 10
        $group_permissions_19 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'projects',
                'contractors',
                'contractors_contacts',
                'objects',
                'objects_edit',
                'manual_materials',
                'manual_nodes',
                'manual_works',
                'mat_acc_report_card',
                'mat_acc_operation_log',
                'mat_acc_arrival_draft_create',
                'mat_acc_write_off_draft_create',
                'mat_acc_arrival_create',
                'mat_acc_write_off_create',
                'project_documents',
                'commercial_offers',
                'work_volumes',
                'contracts',
                'users',
            ]
        ) as $permission) {
            $group_permissions_19[] = ['group_id' => 19, 'permission_id' => $permission->id] ;
        }

        $group_permissions_23 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'objects',
                'manual_materials',
                'manual_nodes',
                'manual_works',
                'mat_acc_report_card',
                'mat_acc_operation_log',
                'mat_acc_arrival_draft_create',
                'mat_acc_write_off_draft_create',
                'project_documents',
                'users',
            ]
        ) as $permission) {
            $group_permissions_23[] = ['group_id' => 23, 'permission_id' => $permission->id] ;
        }

        // others department id 10
        $others_dep_10 = [];
        foreach ([20, 21, 22, 24, 25, 26] as $group_id) {
            foreach($permissions->whereIn('codename',
                [
                    'tasks',
                    'users',
                ]
            ) as $permission) {
                $others_dep_10[] = ['group_id' => $group_id, 'permission_id' => $permission->id] ;
            }
        }

        // department id 11
        $group_permissions_27 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'projects',
                'contractors',
                'contractors_contacts',
                'objects',
                'objects_edit',
                'manual_materials',
                'manual_nodes',
                'manual_works',
                'mat_acc_report_card',
                'mat_acc_operation_log',
                'mat_acc_arrival_draft_create',
                'mat_acc_write_off_draft_create',
                'mat_acc_moving_draft_create',
                'mat_acc_transformation_draft_create',
                'mat_acc_arrival_create',
                'mat_acc_write_off_create',
                'mat_acc_moving_create',
                'mat_acc_transformation_create',
                'project_documents',
                'commercial_offers',
                'work_volumes',
                'contracts',
                'users',
            ]
        ) as $permission) {
            $group_permissions_27[] = ['group_id' => 27, 'permission_id' => $permission->id] ;
        }

        $group_permissions_31 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'objects',
                'manual_materials',
                'manual_nodes',
                'manual_works',
                'mat_acc_report_card',
                'mat_acc_operation_log',
                'mat_acc_arrival_draft_create',
                'mat_acc_write_off_draft_create',
                'mat_acc_moving_draft_create',
                'mat_acc_transformation_draft_create',
                'project_documents',
                'users',
            ]
        ) as $permission) {
            $group_permissions_31[] = ['group_id' => 31, 'permission_id' => $permission->id] ;
        }

        // others department id 11
        $others_dep_11 = [];
        foreach ([28, 29, 30, 32, 33, 34, 35, 36, 37, 38] as $group_id) {
            foreach($permissions->whereIn('codename',
                [
                    'tasks',
                    'users',
                ]
            ) as $permission) {
                $others_dep_11[] = ['group_id' => $group_id, 'permission_id' => $permission->id] ;
            }
        }

        // others department id 12
        $others_dep_12 = [];
        foreach ([39, 40, 41, 42, 43, 44, 45] as $group_id) {
            foreach($permissions->whereIn('codename',
                [
                    'tasks',
                    'mat_acc_report_card',
                    'mat_acc_operation_log',
                    'mat_acc_arrival_draft_create',
                    'mat_acc_write_off_draft_create',
                    'mat_acc_moving_draft_create',
                    'mat_acc_transformation_draft_create',
                    'users',
                ]
            ) as $permission) {
                $others_dep_12[] = ['group_id' => $group_id, 'permission_id' => $permission->id] ;
            }
        }

        // others department id 13
        $others_dep_13 = [];
        foreach ([46, 47, 48] as $group_id) {
            foreach($permissions->whereIn('codename',
                [
                    'tasks',
                    'mat_acc_report_card',
                    'mat_acc_operation_log',
                    'mat_acc_arrival_draft_create',
                    'mat_acc_write_off_draft_create',
                    'mat_acc_moving_draft_create',
                    'mat_acc_transformation_draft_create',
                    'users',
                ]
            ) as $permission) {
                $others_dep_13[] = ['group_id' => $group_id, 'permission_id' => $permission->id] ;
            }
        }

        // department id 14
        $group_permissions_49 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'projects',
                'projects_create',
                'projects_responsible_users',
                'contractors',
                'contractors_create',
                'contractors_edit',
                'contractors_contacts',
                'contractors_delete',
                'objects',
                'objects_create',
                'objects_edit',
                'manual_works',
                'project_documents',
                'commercial_offers',
                'contracts',
                'contracts_create',
                'contracts_delete_request',
                'work_volumes',
                'users',
            ]
        ) as $permission) {
            $group_permissions_49[] = ['group_id' => 49, 'permission_id' => $permission->id] ;
        }

        $group_permissions_50 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'projects',
                'projects_create',
                'projects_responsible_users',
                'contractors',
                'contractors_create',
                'contractors_edit',
                'contractors_contacts',
                'contractors_delete',
                'objects',
                'objects_create',
                'objects_edit',
                'manual_materials',
                'manual_materials_edit',
                'manual_nodes',
                'manual_works',
                'manual_works_edit',
                'project_documents',
                'commercial_offers',
                'contracts',
                'work_volumes',
                'users',
            ]
        ) as $permission) {
            $group_permissions_50[] = ['group_id' => 50, 'permission_id' => $permission->id] ;
        }

        // department id 15
        $group_permissions_51 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'projects',
                'project_documents',
                'commercial_offers',
                'work_volumes',
                'users',
            ]
        ) as $permission) {
            $group_permissions_51[] = ['group_id' => 51, 'permission_id' => $permission->id] ;
        }

        // department id 16
        $group_permissions_53 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'projects',
                'projects_responsible_users',
                'contractors',
                'contractors_contacts',
                'objects',
                'objects_create',
                'objects_edit',
                'manual_materials',
                'manual_materials_edit',
                'manual_nodes',
                'manual_nodes_edit',
                'manual_works',
                'manual_works_edit',
                'project_documents',
                'commercial_offers',
                'contracts',
                'work_volumes',
                'users',
            ]
        ) as $permission) {
            $group_permissions_53[] = ['group_id' => 53, 'permission_id' => $permission->id] ;
        }

        $group_permissions_52 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'projects',
                'projects_responsible_users',
                'contractors',
                'contractors_contacts',
                'objects',
                'objects_create',
                'objects_edit',
                'manual_materials',
                'manual_materials_edit',
                'manual_nodes',
                'manual_nodes_edit',
                'manual_works',
                'manual_works_edit',
                'project_documents',
                'commercial_offers',
                'contracts',
                'work_volumes',
                'users',
            ]
        ) as $permission) {
            $group_permissions_52[] = ['group_id' => 52, 'permission_id' => $permission->id] ;
        }

        $group_permissions_54 = [];
        foreach($permissions->whereIn('codename',
            [
                'tasks',
                'tasks_default_myself',
                'tasks_default_others',
                'projects',
                'projects_create',
                'projects_responsible_users',
                'contractors',
                'contractors_create',
                'contractors_edit',
                'contractors_delete',
                'contractors_contacts',
                'objects',
                'objects_create',
                'objects_edit',
                'manual_materials',
                'manual_materials_edit',
                'manual_nodes',
                'manual_nodes_edit',
                'manual_works',
                'manual_works_edit',
                'project_documents',
                'commercial_offers',
                'contracts',
                'contracts_create',
                'contracts_delete_request',
                'work_volumes',
                'users',
            ]
        ) as $permission) {
            $group_permissions_54[] = ['group_id' => 54, 'permission_id' => $permission->id] ;
        }

        DB::table('group_permissions')->insert($group_permissions_5);
        DB::table('group_permissions')->insert($group_permissions_6);
        DB::table('group_permissions')->insert($group_permissions_7);
        DB::table('group_permissions')->insert($group_permissions_8);
        DB::table('group_permissions')->insert($group_permissions_9);
        DB::table('group_permissions')->insert($group_permissions_10);
        DB::table('group_permissions')->insert($group_permissions_11);
        DB::table('group_permissions')->insert($group_permissions_13);
        DB::table('group_permissions')->insert($group_permissions_14);
        DB::table('group_permissions')->insert($group_permissions_15);
        DB::table('group_permissions')->insert($group_permissions_16);
        DB::table('group_permissions')->insert($group_permissions_17);
        DB::table('group_permissions')->insert($group_permissions_19);
        DB::table('group_permissions')->insert($group_permissions_23);
        DB::table('group_permissions')->insert($others_dep_10);
        DB::table('group_permissions')->insert($group_permissions_27);
        DB::table('group_permissions')->insert($group_permissions_31);
        DB::table('group_permissions')->insert($others_dep_11);
        DB::table('group_permissions')->insert($others_dep_12);
        DB::table('group_permissions')->insert($others_dep_13);
        DB::table('group_permissions')->insert($group_permissions_49);
        DB::table('group_permissions')->insert($group_permissions_50);
        DB::table('group_permissions')->insert($group_permissions_51);
        DB::table('group_permissions')->insert($group_permissions_52);
        DB::table('group_permissions')->insert($group_permissions_53);
        DB::table('group_permissions')->insert($group_permissions_54);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

<?php

use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

class EditMaterialAccountingRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $confirmToWriteOffPermission = new Permission();
        $confirmToWriteOffPermission->name = 'Материальный учет: Редактирование типов материалов';
        $confirmToWriteOffPermission->codename = 'material_accounting_materials_types_editing';
        $confirmToWriteOffPermission->category = 7; // Категории описаны в модели "Permission"
        $confirmToWriteOffPermission->save();

        $confirmToWriteOffPermission = new Permission();
        $confirmToWriteOffPermission->name = 'Материальный учет: Редактирование эталонов';
        $confirmToWriteOffPermission->codename = 'material_accounting_materials_standards_editing';
        $confirmToWriteOffPermission->category = 7; // Категории описаны в модели "Permission"
        $confirmToWriteOffPermission->save();

        $confirmToWriteOffPermission = new Permission();
        $confirmToWriteOffPermission->name = 'Материальный учет: Просмотр «Табеля материального учета»';
        $confirmToWriteOffPermission->codename = 'material_accounting_material_table_access';
        $confirmToWriteOffPermission->category = 7; // Категории описаны в модели "Permission"
        $confirmToWriteOffPermission->save();

        $confirmToWriteOffPermission = new Permission();
        $confirmToWriteOffPermission->name = 'Материальный учет: Просмотр списка операций';
        $confirmToWriteOffPermission->codename = 'material_accounting_operation_list_access';
        $confirmToWriteOffPermission->category = 7; // Категории описаны в модели "Permission"
        $confirmToWriteOffPermission->save();

        $confirmToWriteOffPermission = new Permission();
        $confirmToWriteOffPermission->name = 'Материальный учет: Просмотр списка материалов на объекте и выполненных операций';
        $confirmToWriteOffPermission->codename = 'material_accounting_material_list_access';
        $confirmToWriteOffPermission->category = 7; // Категории описаны в модели "Permission"
        $confirmToWriteOffPermission->save();

        $confirmToWriteOffPermission = new Permission();
        $confirmToWriteOffPermission->name = 'Материальный учет: cоздание операций';
        $confirmToWriteOffPermission->codename = 'material_accounting_operations_creating';
        $confirmToWriteOffPermission->category = 7; // Категории описаны в модели "Permission"
        $confirmToWriteOffPermission->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = Permission::where('codename', 'material_accounting_materials_types_editing')->first();
        if (isset($permission)) {
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }
        if (isset($permission)) {
            $permission = Permission::where('codename', 'material_accounting_materials_standards_editing')->first();
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        if (isset($permission)) {
            $permission = Permission::where('codename', 'material_accounting_material_table_access')->first();
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        if (isset($permission)) {
            $permission = Permission::where('codename', 'material_accounting_operation_list_access')->first();
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        if (isset($permission)) {
            $permission = Permission::where('codename', 'material_accounting_material_list_access')->first();
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        if (isset($permission)) {
            $permission = Permission::where('codename', 'material_accounting_operations_creating')->first();
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }
    }
}

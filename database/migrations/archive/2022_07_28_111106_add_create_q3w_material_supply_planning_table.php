<?php

use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('q3w_material_supply_planning', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->integer('project_object_id')->unsigned()->comment('Идентификатор объекта');
            $table->integer('brand_id')->unsigned()->comment('Идентификатор марки материала');
            $table->float('quantity')->unsigned()->comment('Количество (в единицах измерения)');
            $table->integer('amount')->unsigned()->comment('Количество (в штуках)');
            $table->float('planned_project_weight')->unsigned()->comment('Запланированный вес материала по проекту');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_object_id')->references('id')->on('projects');
            $table->foreign('brand_id')->references('id')->on('q3w_material_brands');
        });

        DB::statement("ALTER TABLE q3w_material_supply_planning COMMENT 'Планирование поставок материалов на объекты'");

        $permission = new Permission();
        $permission->name = 'Материальный учет: Доступ к режиму "Планирование поставок"';
        $permission->codename = 'material_supply_planning_access';
        $permission->category = 7; // Категории описаны в модели "Permission"
        $permission->save();

        $permission = new Permission();
        $permission->name = 'Материальный учет: Редактирование данных в режиме "Планирование поставок"';
        $permission->codename = 'material_supply_planning_editing';
        $permission->category = 7; // Категории описаны в модели "Permission"
        $permission->save();

        Schema::create('q3w_material_supply_expected_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('supply_planning_id')->unsigned()->comment('Запланированный материал');
            $table->integer('contractor_id')->unsigned()->comment('Поставщик');
            $table->float('quantity')->unsigned()->comment('Количество (в единицах измерения)');
            $table->integer('amount')->unsigned()->comment('Количество (в штуках)');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supply_planning_id', 'supply_planning_id_foreign')->references('id')->on('q3w_material_supply_planning');
            $table->foreign('contractor_id')->references('id')->on('contractors');
        });

        DB::statement("ALTER TABLE q3w_material_supply_planning COMMENT 'Планирование поставок материалов на объекты'");

        Schema::create('q3w_material_supply_materials', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('supply_planning_id')->unsigned()->comment('Запланированный материал');
            $table->bigInteger('material_id')->unsigned()->comment('Выбранный материал');
            $table->integer('amount')->unsigned()->comment('Количество выбранного материала');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supply_planning_id')->references('id')->on('q3w_material_supply_planning');
            $table->foreign('material_id')->references('id')->on('q3w_materials');
        });

        DB::statement("ALTER TABLE q3w_material_supply_materials COMMENT 'План по материалам на объекте'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('q3w_material_supply_materials');
        Schema::dropIfExists('q3w_material_supply_expected_deliveries');
        Schema::dropIfExists('q3w_material_supply_planning');

        $permission = Permission::where('codename', 'material_supply_planning_access')->first();
        if (isset($permission)) {
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        $permission = Permission::where('codename', 'material_supply_planning_editing')->first();
        if (isset($permission)) {
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }
    }
};

<?php

use App\Models\LaborSafety\LaborSafetyWorkerType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLaborSafety extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('labor_safety_requests', function (Blueprint $table) {
            $table->bigInteger('project_manager_employee_id')->unsigned()->nullable()->comment('Руководитель проекта');
            $table->bigInteger('sub_project_manager_employee_id')->unsigned()->nullable()->comment('Заместитель руководителя проекта');

            $table->foreign('project_manager_employee_id')->references('id')->on('employees');
            $table->foreign('sub_project_manager_employee_id')->references('id')->on('employees');

            $laborSafetyWorkerTypesArray = [
                'Руководитель проектов',
                'Заместитель руководителя проектов'
            ];

            foreach ($laborSafetyWorkerTypesArray as $laborSafetyWorkerTypesElement) {
                $laborSafetyWorkerTypes = new LaborSafetyWorkerType([
                    'name' => $laborSafetyWorkerTypesElement
                ]);
                $laborSafetyWorkerTypes->save();
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('labor_safety_requests', function (Blueprint $table) {
            $table->dropForeign(['project_manager_employee_id']);
            $table->dropForeign(['sub_project_manager_employee_id']);

            $table->dropColumn('project_manager_employee_id');
            $table->dropColumn('sub_project_manager_employee_id');

        });
    }
}
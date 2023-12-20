<?php

use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaskXlsxExportRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $taskReportXLSXExportPermission = new Permission();
        $taskReportXLSXExportPermission->name = "Коммерческий блок: Доступ к отчету по задачам и КП";
        $taskReportXLSXExportPermission->codename = "commercial_block_task_report_xlsx_export_access";
        $taskReportXLSXExportPermission->category = 1; // Категории описаны в модели "Permission"
        $taskReportXLSXExportPermission->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $taskReportXLSXExportPermission = Permission::where('codename', 'commercial_block_task_report_xlsx_export_access')->first();

        UserPermission::where('permission_id', $taskReportXLSXExportPermission->id)->forceDelete();

        $taskReportXLSXExportPermission->forceDelete();
    }
}

<?php

use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $taskReportXLSXExportPermission = new Permission();
        $taskReportXLSXExportPermission->name = 'Коммерческий блок: Доступ к отчету по задачам и КП';
        $taskReportXLSXExportPermission->codename = 'commercial_block_task_report_xlsx_export_access';
        $taskReportXLSXExportPermission->category = 1; // Категории описаны в модели "Permission"
        $taskReportXLSXExportPermission->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $taskReportXLSXExportPermission = Permission::where('codename', 'commercial_block_task_report_xlsx_export_access')->first();

        UserPermission::where('permission_id', $taskReportXLSXExportPermission->id)->forceDelete();

        $taskReportXLSXExportPermission->forceDelete();
    }
};

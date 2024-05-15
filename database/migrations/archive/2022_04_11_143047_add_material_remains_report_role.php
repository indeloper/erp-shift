<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $taskReportXLSXExportPermission = new Permission();
        $taskReportXLSXExportPermission->name = 'Материальный учет: Доступ к отчету Остатки материалов"';
        $taskReportXLSXExportPermission->codename = 'material_accounting_material_remains_report_access';
        $taskReportXLSXExportPermission->category = 7; // Категории описаны в модели "Permission"
        $taskReportXLSXExportPermission->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //
    }
};

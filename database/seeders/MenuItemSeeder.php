<?php

namespace Database\Seeders;

use App\Models\Menu\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = MenuItem::factory()->create([
            'title' => 'Задачи',
            'parent_id' => null,
            'route_name' => 'tasks::index',
            'icon_path' => '<i class="pe-7s-timer"></i>',
            'gates' => [
                'tasks',
                'dashbord',
            ],
            'actives' => [
                'tasks',
                'tasks/*',
            ],
            'status' => true,
        ]);

        $projects = MenuItem::factory()->create([
            'title' => 'Проекты',
            'parent_id' => null,
            'route_name' => 'projects::index',
            'icon_path' => '<i class="pe-7s-display1"></i>',
            'gates' => [
                'projects',
            ],
            'actives' => [
                'projects',
                'projects/*',
            ],
            'status' => true,
        ]);

        $commerce = MenuItem::factory()->create([
            'title' => 'Коммерция',
            'parent_id' => null,
            'route_name' => null,
            'icon_path' => '<i class="pe-7s-portfolio"></i>',
            'gates' => [
                'contractors',
                'objects',
            ],
            'actives' => [
                'contractors',
                'contractors/*',
                'objects',
                'objects/*',
                'building/works',
                'tasks/filter-tasks-report',
                'building/materials',
            ],
            'status' => true,
        ]);

        $contractors = MenuItem::factory()->create([
            'title' => 'Контрагенты',
            'parent_id' => $commerce->id,
            'route_name' => 'contractors::index',
            'icon_path' => '<i class="pe-7s-users pe-7s-mini"></i>',
            'gates' => [
                'contractors',
            ],
            'actives' => [
                'contractors',
                'contractors/*',
            ],
            'status' => true,
        ]);

        $objects = MenuItem::factory()->create([
            'title' => 'Объекты',
            'parent_id' => $commerce->id,
            'route_name' => 'objects::base-template',
            'icon_path' => '<i class="pe-7s-culture pe-7s-mini"></i>',
            'gates' => [
                'objects',
            ],
            'actives' => [
                'objects',
                'objects/*',
            ],
            'status' => true,
        ]);

        $manualMaterials = MenuItem::factory()->create([
            'title' => 'Материалы',
            'parent_id' => $commerce->id,
            'route_name' => 'building::materials::index',
            'icon_path' => '<i class="pe-7s-diamond pe-7s-mini"></i>',
            'gates' => [
                'manual_materials',
            ],
            'actives' => [
                'building/materials',
                'building/materials/*',
            ],
            'status' => true,
        ]);

        $manualWorks = MenuItem::factory()->create([
            'title' => 'Работы',
            'parent_id' => $commerce->id,
            'route_name' => 'building::works::index',
            'icon_path' => '<i class="pe-7s-config pe-7s-mini"></i>',
            'gates' => [
                'manual_works',
            ],
            'actives' => [
                'building/works',
                'building/works/*',
                'building/work_groups/*',
                'building/work_groups',
            ],
            'status' => true,
        ]);

        $commercial_block_task_report_xlsx_export_access
            = MenuItem::factory()->create([
                'title' => 'Отчет по задачам и КП',
                'parent_id' => $commerce->id,
                'route_name' => 'tasks.filter-tasks-report',
                'icon_path' => '<i class="pe-7s-download pe-7s-mini"></i>',
                'gates' => [
                    'commercial_block_task_report_xlsx_export_access',
                ],
                'actives' => [
                    'building/works',
                    'building/works/*',
                    'building/work_groups/*',
                    'building/work_groups',
                ],
                'status' => true,
            ]);

        $materials = MenuItem::factory()->create([
            'title' => 'Строительство',
            'parent_id' => null,
            'route_name' => null,
            'icon_path' => '<i class="pe-7s-plugin"></i>',
            'gates' => [
                'material_accounting_materials_types_editing',
                'material_accounting_materials_standards_editing',
                'material_accounting_material_table_access',
                'material_accounting_operation_list_access',
                'material_accounting_material_list_access',
            ],
            'actives' => [
                'materials/*',
                '*fuel_tank*',
                '*our_technic_tickets*',
                '*defects*',
                '*tech_acc*',
                '*vehicles*',
            ],
            'status' => true,
        ]);

        $material_accounting_operation_list_access
            = MenuItem::factory()->create([
                'title' => 'Операции',
                'parent_id' => $materials->id,
                'route_name' => 'materials.operations.index',
                'icon_path' => '<i class="pe-7s-note2 pe-7s-mini"></i>',
                'gates' => [
                    'material_accounting_operation_list_access',
                ],
                'actives' => [
                    '/materials/operations/all',
                    '/materials/operations/all/*',
                ],
                'status' => true,
            ]);

        $material_accounting_material_list_access
            = MenuItem::factory()->create([
                'title' => 'Материалы',
                'parent_id' => $materials->id,
                'route_name' => 'materials.index',
                'icon_path' => '<i class="pe-7s-note2 pe-7s-mini"></i>',
                'gates' => [
                    'material_accounting_material_list_access',
                ],
                'actives' => [
                    'materials/material',
                    'materials/material/*',
                ],
                'status' => true,
            ]);

        $material_supply_planning_access = MenuItem::factory()->create([
            'title' => 'Планирование поставок',
            'parent_id' => $materials->id,
            'route_name' => 'materials.supply-planning.index',
            'icon_path' => '<i class="pe-7s-note2 pe-7s-mini"></i>',
            'gates' => [
                'material_supply_planning_access',
            ],
            'actives' => [
                'materials/supply-planning',
                'materials/supply-planning/*',
            ],
            'status' => true,
        ]);

        $material_accounting_material_table_access
            = MenuItem::factory()->create([
                'title' => 'Табель учета материалов',
                'parent_id' => $materials->id,
                'route_name' => 'materials.table',
                'icon_path' => '<i class="pe-7s-note2 pe-7s-mini"></i>',
                'gates' => [
                    'material_accounting_material_table_access',
                ],
                'actives' => [
                    '/materials/table',
                    '/materials/table/*',
                ],
                'status' => true,
            ]);

        $material_accounting_material_remains_report_access
            = MenuItem::factory()->create([
                'title' => 'Остатки материалов',
                'parent_id' => $materials->id,
                'route_name' => 'materials.remains',
                'icon_path' => '<i class="pe-7s-note2 pe-7s-mini"></i>',
                'gates' => [
                    'material_accounting_material_remains_report_access',
                ],
                'actives' => [
                    '/materials/remains',
                    '/materials/remains/*',
                ],
                'status' => true,
            ]);

        $material_accounting_objects_remains_report_access
            = MenuItem::factory()->create([
                'title' => 'Остатки на объектах',
                'parent_id' => $materials->id,
                'route_name' => 'materials.objects.remains',
                'icon_path' => '<i class="pe-7s-note2 pe-7s-mini"></i>',
                'gates' => [
                    'material_accounting_material_remains_report_access',
                ],
                'actives' => [
                    '/materials/objects-remains',
                    '/materials/objects-remains/*',
                ],
                'status' => true,
            ]);

        $material_accounting_materials_standards_editing
            = MenuItem::factory()->create([
                'title' => 'Эталоны',
                'parent_id' => $materials->id,
                'route_name' => 'materials.standards.index',
                'icon_path' => '<i class="pe-7s-diamond pe-7s-mini"></i>',
                'gates' => [
                    'material_accounting_materials_standards_editing',
                ],
                'actives' => [
                    'materials/material-standard',
                    'materials/material-standard/*',
                ],
                'status' => true,
            ]);

        $material_accounting_materials_types_editing
            = MenuItem::factory()->create([
                'title' => 'Типы материалов',
                'parent_id' => $materials->id,
                'route_name' => 'materials.types.index',
                'icon_path' => '<i class="pe-7s-menu pe-7s-mini"></i>',
                'gates' => [
                    'material_accounting_materials_types_editing',
                ],
                'actives' => [
                    'materials/material-type',
                    'materials/material-type/*',
                ],
                'status' => true,
            ]);

        $technics_access_permission = MenuItem::factory()->create([
            'title' => 'Учет техники',
            'parent_id' => null,
            'route_name' => null,
            'icon_path' => '<i class="pe-7s-note2"></i>',
            'gates' => [
                'technics_access_permission',
            ],
            'actives' => [
                'building/tech_acc/technic/*',
            ],
            'status' => true,
        ]);

        $ourTechnicList = MenuItem::factory()->create([
            'title' => 'Список техники',
            'parent_id' => $technics_access_permission->id,
            'route_name' => 'building::tech_acc::technic::ourTechnicList::getPageCore',
            'icon_path' => '<i class="pe-7s-mini"><img src="'
                .mix('img/crane.svg')
                .'" alt="" width="20" class="pull-left" style="margin-bottom: 5px"></i>',
            'gates' => [
            ],
            'actives' => [
                'building/tech_acc/technic/ourTechnicList*',
            ],
            'status' => true,
        ]);

        $technics_movement_crud = MenuItem::factory()->create([
            'title' => 'Перемещения техники',
            'parent_id' => $technics_access_permission->id,
            'route_name' => 'building::tech_acc::technic::movements::getPageCore',
            'icon_path' => '<i class="pe-7s-mini"><img src="'
                .mix('img/crane.svg')
                .'" alt="" width="20" class="pull-left" style="margin-bottom: 5px"></i>',
            'gates' => [
                'technics_movement_crud',
                'technics_movement_read',
                'technics_processing_movement_standart_sized_equipment',
                'technics_processing_movement_oversized_equipment',
            ],
            'actives' => [
                'building/tech_acc/technic/movements*',
            ],
            'status' => true,
        ]);

        $technics_brands_models_categories_read_create_update_delete
            = MenuItem::factory()->create([
                'title' => 'Категории техники',
                'parent_id' => $technics_access_permission->id,
                'route_name' => 'building::tech_acc::technic::technicCategory::getPageCore',
                'icon_path' => '<i class="pe-7s-mini"><img src="'
                    .mix('img/crane.svg')
                    .'" alt="" width="20" class="pull-left" style="margin-bottom: 5px"></i>',
                'gates' => [
                    'technics_brands_models_categories_read_create_update_delete',
                ],
                'actives' => [
                    'building/tech_acc/technic/technicCategory*',

                ],
                'status' => true,
            ]);

        $technicBrand = MenuItem::factory()->create([
            'title' => 'Марки техники',
            'parent_id' => $technics_access_permission->id,
            'route_name' => 'building::tech_acc::technic::technicBrand::getPageCore',
            'icon_path' => '<i class="pe-7s-mini"><img src="'
                .mix('img/crane.svg')
                .'" alt="" width="20" class="pull-left" style="margin-bottom: 5px"></i>',
            'gates' => [
                'technics_brands_models_categories_read_create_update_delete',
            ],
            'actives' => [
                'building/tech_acc/technic/technicBrand',

            ],
            'status' => true,
        ]);

        $technicBrandModel = MenuItem::factory()->create([
            'title' => 'Модели техники',
            'parent_id' => $technics_access_permission->id,
            'route_name' => 'building::tech_acc::technic::technicBrandModel::getPageCore',
            'icon_path' => '<i class="pe-7s-mini"><img src="'
                .mix('img/crane.svg')
                .'" alt="" width="20" class="pull-left" style="margin-bottom: 5px"></i>',
            'gates' => [
                'technics_brands_models_categories_read_create_update_delete',
            ],
            'actives' => [
                'building/tech_acc/technic/mtechnicBrandModel*',

            ],
            'status' => true,
        ]);

        $fuel = MenuItem::factory()->create([
            'title' => 'Учет топлива',
            'parent_id' => null,
            'route_name' => null,
            'icon_path' => '<i class="pe-7s-note2"></i>',
            'gates' => [
                'fuel_tanks_access',
                'fuel_tank_flows_access',
                'fuel_tank_operations_report_advanced_filter_settings_access',
                'fuel_tanks_movements_report_access',
            ],
            'actives' => [
                'building/tech_acc/fuel/*',

            ],
            'status' => true,
        ]);

        $fuel_tanks_access = MenuItem::factory()->create([
            'title' => 'Топливные емкости',
            'parent_id' => $fuel->id,
            'route_name' => 'building::tech_acc::fuel::tanks::getPageCore',
            'icon_path' => '<i class="pe-7s-paint-bucket pe-7s-mini"></i>',
            'gates' => [
                'fuel_tanks_access',
            ],
            'actives' => [
                'building/tech_acc/fuel/tank*',

            ],
            'status' => true,
        ]);

        $fuel_tank_flows_access = MenuItem::factory()->create([
            'title' => 'Топливный журнал',
            'parent_id' => $fuel->id,
            'route_name' => 'building::tech_acc::fuel::fuelFlow::getPageCore',
            'icon_path' => '<i class="pe-7s-drop pe-7s-mini"></i>',
            'gates' => [
                'fuel_tank_flows_access',
            ],
            'actives' => [
                'building/tech_acc/fuel/fuelFlow*',

            ],
            'status' => true,
        ]);

        $fuel_tank_operations_report_advanced_filter_settings_access
            = MenuItem::factory()->create([
                'title' => 'Отчет по топливу',
                'parent_id' => $fuel->id,
                'route_name' => 'building::tech_acc::fuel::reports::fuelTankPeriodReport::getPageCore',
                'icon_path' => '<i class="pe-7s-news-paper pe-7s-mini"></i>',
                'gates' => [
                    'fuel_tank_operations_report_advanced_filter_settings_access',
                ],
                'actives' => [
                    'building/tech_acc/fuel/reports/fuelTankPeriodReport*',
                ],
                'status' => true,
            ]);

        $fuel_tanks_movements_report_access = MenuItem::factory()->create([
            'title' => 'Перемещение емкостей',
            'parent_id' => $fuel->id,
            'route_name' => 'building::tech_acc::fuel::reports::tanksMovementReport::getPageCore',
            'icon_path' => '<i class="pe-7s-news-paper pe-7s-mini"></i>',
            'gates' => [
                'fuel_tanks_movements_report_access',
            ],
            'actives' => [
                'building/tech_acc/fuel/reports/tanksMovementReport*',
            ],
            'status' => true,
        ]);

        $documents = MenuItem::factory()->create([
            'title' => 'Документооборот',
            'parent_id' => null,
            'route_name' => null,
            'icon_path' => '<i class="pe-7s-folder"></i>',
            'gates' => [
                'project_documents',
                'commercial_offers',
                'work_volumes',
                'contracts',
            ],
            'actives' => [
                'project-object-documents',
                'project_documents',
                'project_documents/*',
                'commercial_offers',
                'commercial_offers/*',
                'contracts',
                'contracts/*',
                'work_volumes',
                'work_volumes/*',
            ],
            'status' => true,
        ]);

        $project_object_documents_access = MenuItem::factory()->create([
            'title' => 'Площадка ⇆ Офис',
            'parent_id' => $documents->id,
            'route_name' => 'project-object-documents',
            'icon_path' => 'ПО',
            'gates' => [
                'project_object_documents_access',
            ],
            'actives' => [
                'project-object-documents',
                'project-object-documents/*',
            ],
            'status' => true,
        ]);

        $project_documents = MenuItem::factory()->create([
            'title' => 'Проектная документация',
            'parent_id' => $documents->id,
            'route_name' => 'project_documents::index',
            'icon_path' => 'ПД',
            'gates' => [
                'project_documents',
            ],
            'actives' => [
                'project_documents',
                'project_documents/*',
            ],
            'status' => true,
        ]);

        $commercial_offers = MenuItem::factory()->create([
            'title' => 'Коммерч. предложения',
            'parent_id' => $documents->id,
            'route_name' => 'commercial_offers::index',
            'icon_path' => 'КП',
            'gates' => [
                'commercial_offers',
            ],
            'actives' => [
                'commercial_offers',
                'commercial_offers/*',
            ],
            'status' => true,
        ]);

        $contracts = MenuItem::factory()->create([
            'title' => 'Договоры',
            'parent_id' => $documents->id,
            'route_name' => 'contracts::index',
            'icon_path' => 'Д',
            'gates' => [
                'contracts',
            ],
            'actives' => [
                'contracts',
                'contracts/*',
            ],
            'status' => true,
        ]);

        $work_volumes = MenuItem::factory()->create([
            'title' => 'Объемы работ',
            'parent_id' => $documents->id,
            'route_name' => 'work_volumes::index',
            'icon_path' => 'ОР',
            'gates' => [
                'work_volumes',
            ],
            'actives' => [
                'work_volumes',
                'work_volumes/*',
            ],
            'status' => true,
        ]);

        $labor = MenuItem::factory()->create([
            'title' => 'Охрана труда',
            'parent_id' => null,
            'route_name' => null,
            'is_su' => true,
            'icon_path' => '<i class="pe-7s-folder"></i>',
            'gates' => [
                'labor_safety_order_creation',
                'labor_safety_order_list_access',
                'labor_safety_order_types_editing',
            ],
            'actives' => [
                'labor-safety',
                'labor-safety/*',
            ],
            'status' => true,
        ]);

        $labor_safety_order_creation = MenuItem::factory()->create([
            'title' => 'Заявки и приказы',
            'parent_id' => $labor->id,
            'route_name' => 'labor-safety.orders-and-requests.index',
            'icon_path' => '<i class="fas fa-envelope"></i>',
            'gates' => [
                'labor_safety_order_creation',
            ],
            'actives' => [
                'labor-safety/orders-and-requests',
            ],
            'status' => true,
        ]);

        $labor_safety_order_types_editing = MenuItem::factory()->create([
            'title' => 'Шаблоны приказов',
            'parent_id' => $labor->id,
            'route_name' => 'labor-safety.order-types.index',
            'icon_path' => '<i class="fas fa-envelope"></i>',
            'gates' => [
                'labor_safety_order_types_editing',
            ],
            'actives' => [
                'labor-safety/templates',
            ],
            'status' => true,
        ]);

        $is_su = MenuItem::factory()->create([
            'title' => 'Администрирование',
            'parent_id' => null,
            'route_name' => null,
            'is_su' => true,
            'icon_path' => '<i class="pe-7s-folder"></i>',
            'actives' => [
                'admin',
                'admin/*',
                'admin/notifications',
                '/admin/telegram-route-templates',
            ],
            'status' => true,
        ]);

        $notifications = MenuItem::factory()->create([
            'title' => 'Рассылка уведомлений',
            'parent_id' => $is_su->id,
            'route_name' => 'admin.notifications',
            'icon_path' => '<i class="fas fa-envelope"></i>',
            'actives' => [
                'admin/notifications',
            ],
            'status' => true,
        ]);

        $accounting_data = MenuItem::factory()->create([
            'title' => 'Проверка мат. учета',
            'parent_id' => $is_su->id,
            'route_name' => 'admin.validate-material-accounting_data',
            'icon_path' => '<i class="fas fa-check"></i>',
            'actives' => [
                '/admin/validate-material-accounting-data',
            ],
            'status' => true,
        ]);

        $permissions = MenuItem::factory()->create([
            'title' => 'Управление доступами',
            'parent_id' => $is_su->id,
            'route_name' => 'admin.permissions',
            'icon_path' => '<i class="fas fa-check"></i>',
            'actives' => [
                '/admin/permissions',
            ],
            'status' => true,
        ]);

        $permissions = MenuItem::factory()->create([
            'title' => 'Роли пользователей',
            'parent_id' => $is_su->id,
            'route_name' => 'admin.permissions',
            'icon_path' => '<i class="fas fa-check"></i>',
            'actives' => [
                '/admin/permissions',
            ],
            'status' => true,
        ]);

        $permissions = MenuItem::factory()->create([
            'title' => 'Шаблоны телеграм',
            'parent_id' => $is_su->id,
            'route_name' => 'admin.telegram-route-templates::getPageCore',
            'icon_path' => '<i class="fas fa-envelope"></i>',
            'actives' => [
                '//admin/telegram-route-templates',
            ],
            'status' => false,
        ]);

        $users = MenuItem::factory()->create([
            'title' => 'Сотрудники',
            'parent_id' => null,
            'route_name' => 'users::index',
            'icon_path' => '<i class="pe-7s-id"></i>',
            'gates' => [
                'users',
            ],
            'actives' => [
                'users',
                'users/*',
            ],
            'status' => true,
        ]);

        //        $support = MenuItem::factory()->create([
        //            'title'      => 'Техническая поддержка',
        //            'parent_id'  => null,
        //            'route_name' => 'support::index',
        //            'icon_path'  => '<i class="pe-7s-help1"></i>',
        //            'actives'     => [
        //                'support',
        //                'support/*',
        //            ],
        //            'status'     => true,
        //        ]);
    }
}

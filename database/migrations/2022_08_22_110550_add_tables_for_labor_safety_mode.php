<?php

use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyOrderTypeCategory;
use App\Models\LaborSafety\LaborSafetyRequestStatus;
use App\Models\Permission;
use App\Models\Company\Company;
use App\Models\Timesheet\EmployeesReportGroup;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTablesForLaborSafetyMode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*if (!Schema::hasColumn('employees_report_groups', 'individual_1c_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('individual_1c_uid')->nullable()->comment('Уникальный идентификатор физического лица в 1С');
            });
            DB::statement("ALTER TABLE users COMMENT 'Список пользователей системы'");
        }*/

        if (!Schema::hasTable('employees_report_groups')) {
            Schema::create('employees_report_groups', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('name')->comment('Наименование отчетной группы');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE employees_report_groups COMMENT 'Отчетные группы сотрудников. Используются в модуле «Учет рабочего времени» для формирования отчета в excel'");

            $reportGroupArray = ['Прорабы', 'Руководители', 'База', 'Механики', 'Сотрудники шпунт', 'Крановщики', 'Офис', 'Геодезическая служба', 'Производственный участок - сваи', 'УМиТ'];
            foreach ($reportGroupArray as $reportGroupElement) {
                $reportGroup = new EmployeesReportGroup([
                    'name' => $reportGroupElement
                ]);
                $reportGroup->save();
            }
        }

        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('company_1c_uid')->comment('Уникальный идентификатор в 1С');
                $table->string('name')->comment('Наименование организации');
                $table->string('full_name')->comment('Полное наименование организации');
                $table->string('legal_address')->comment('Юридический адрес');
                $table->string('actual_address')->nullable()->comment('Фактический адрес');
                $table->string('phone')->nullable()->comment('Телефон');
                $table->string('ogrn')->nullable()->comment('ОГРН');
                $table->string('inn')->nullable()->comment('ИНН');
                $table->string('web_site')->nullable()->comment('Адрес сайта');
                $table->string('email')->comment('Email');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE companies COMMENT 'Список организаций'");

            $company = new Company([
                'company_1c_uid' => 'empty',
                'name' => 'ООО «СК ГОРОД»',
                'full_name' => 'Общество с ограниченной ответственностью «СК ГОРОД»',
                'legal_address' => '',
                'actual_address' => '196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, лит. А, каб. 406',
                'phone' => '+7 (812) 335-90-90',
                'ogrn' => '1107847027045',
                'inn' => '7807348494',
                'web_site' => 'www.sk-gorod.com',
                'email' => 'info@sk-gorod.com',
            ]);
            $company->save();

            $company = new Company([
                'company_1c_uid' => 'empty',
                'name' => 'ООО «ГОРОД»',
                'full_name' => 'Общество с ограниченной ответственностью «ГОРОД»',
                'legal_address' => '196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, лит. А, помещ. 56-н, каб. 406',
                'actual_address' => '196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, лит. А, каб. 406',
                'phone' => '+7 (812) 335-90-90',
                'ogrn' => '1167847146917',
                'inn' => '7807115228',
                'web_site' => 'www.sk-gorod.com',
                'email' => 'info@sk-gorod.com'
            ]);
            $company->save();

            $company = new Company([
                'company_1c_uid' => 'empty',
                'name' => 'ООО «РЕНТМАСТЕР»',
                'full_name' => 'Общество с ограниченной ответственностью «РЕНТМАСТЕР»',
                'legal_address' => '196128, г. Санкт-Петербург, Кузнецовская ул., д. 19, лит. А, помещ. 12Н (№15), офис. 409',
                'actual_address' => '196128, г. Санкт-Петербург, Кузнецовская ул., д. 19, лит. А, помещ. 12Н (№15), офис. 409',
                'phone' => '',
                'ogrn' => '1197847099229',
                'inn' => '7807227475',
                'web_site' => '',
                'email' => 'rentmaster10@yandex.ru'
            ]);
            $company->save();

            $company = new Company([
                'company_1c_uid' => 'empty',
                'name' => 'ООО «СТРОЙМАСТЕР»',
                'full_name' => 'Общество с ограниченной ответственностью «СТРОЙМАСТЕР»',
                'legal_address' => '196128, г. Санкт-Петербург, Кузнецовская ул., д. 19, лит. А, помещ. 12Н (№18)',
                'actual_address' => '196128, г. Санкт-Петербург, Кузнецовская ул., д. 19, лит. А, помещ. 12Н (№18)',
                'phone' => '+7 (812) 303-90-53',
                'ogrn' => '1147847349165',
                'inn' => '7842528806',
                'web_site' => '',
                'email' => 'stroymaster9@yandex.ru'
            ]);
            $company->save();
        }

        if (!Schema::hasTable('company_report_template_types')) {
            Schema::create('company_report_template_types', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('name')->unsigned()->comment('Значение');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE company_report_template_types COMMENT 'Типы шаблонов для бланков компаний'");

            $reportTemplateTypeArray = ['header', 'footer'];
            foreach ($reportTemplateTypeArray as $reportTemplateTypeElement) {
                $reportTemplateType = new EmployeesReportGroup([
                    'name' => $reportTemplateTypeElement
                ]);
                $reportTemplateType->save();
            }
        }

        if (!Schema::hasTable('company_report_templates')) {
            Schema::create('company_report_templates', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('company_id')->unsigned()->comment('ID организации');
                $table->integer('template_type')->unsigned()->comment('Тип шаблона');
                $table->text('template')->comment('Шаблон');

                $table->foreign('company_id')->references('id')->on('companies');
                $table->foreign('template_type')->references('id')->on('company_report_template_types');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE company_report_templates COMMENT 'Шаблоны для отчетов по компаниям'");

            $companiesReportTemplateArray = ['1|1|<table style="height: 142px; width: 100%; border-collapse: collapse;" border="1"><tbody><tr style="height: 142px;"><td style="width: 50%;"><img src="https://erp.sk-gorod.com/img/sk-gorod-logo.png" /></td><td style="width: 50%;"><p style="text-align: right;">{company_requisites}</p></td></tr></tbody></table>',
            ];

            foreach ($companiesReportTemplateArray as $companiesReportTemplateElement) {
                $laborSafetyOrderType = new LaborSafetyOrderType([
                    'company_id' => explode('|', $companiesReportTemplateElement)[0],
                    'template_type' => explode('|', $companiesReportTemplateElement)[1],
                    'template' => explode('|', $companiesReportTemplateElement)[2],

                ]);
                $laborSafetyOrderType->save();
            }
        }

        if (!Schema::hasTable('employees_1c_subdivisions')) {
            Schema::create('employees_1c_subdivisions', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('subdivision_parent_id')->unsigned()->comment('Уникальный идентификатор');
                $table->string('name')->comment('Наименование должности');
                $table->string('subdivisions_1c_uid')->comment('Уникальный идентификатор 1С');
                $table->integer('company_id')->unsigned()->comment('ID организации');

                $table->foreign('company_id')->references('id')->on('companies');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE employees_1c_subdivisions COMMENT 'Список подразделений, синхронизировано с 1С'");
        }

        if (!Schema::hasTable('employees_1c_posts')) {
            Schema::create('employees_1c_posts', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('name')->comment('Наименование должности');
                $table->string('post_1c_uid')->comment('Наименование отчетной группы');
                $table->integer('subdivision_id')->unsigned()->comment('ID подразделения');
                $table->integer('company_id')->unsigned()->comment('ID организации');

                $table->foreign('subdivision_id')->references('id')->on('employees_1c_subdivisions');
                $table->foreign('company_id')->references('id')->on('companies');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE employees_1c_posts COMMENT 'Должности сотрудников, синхронизированные c 1С'");
        }

        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор');
                $table->integer('user_id')->unsigned()->comment('Пользователь');

                $table->string('employee_1c_name')->comment('Имя сотрудника в 1С');

                $table->string('personnel_number')->comment('Табельный номер сотрудника');
                $table->string('employee_1c_uid')->comment('Уникальный идентификатор сотрудника в 1С');
                $table->integer('employee_1c_post_id')->unsigned()->comment('Уникальный идентификатор должности сотрудника в 1С');
                $table->integer('employee_1c_subdivision_id')->unsigned()->comment('Уникальный идентификатор подразделения сотрудника в 1С');
                $table->integer('company_id')->unsigned()->comment('Уникальный идентификатор организации, в которой работает сотрудник, в 1С');

                $table->date('employment_date')->comment('Дата приема на работу');
                $table->date('dismissal_date')->comment('Дата увольнения');

                $table->integer('report_group_id')->unsigned()->comment('Наименование отчетной группы');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('report_group_id')->references('id')->on('employees_report_groups');
                $table->foreign('company_id')->references('id')->on('companies');
                $table->foreign('employee_1c_subdivision_id')->references('id')->on('employees_1c_subdivisions');
            });
            DB::statement("ALTER TABLE employees COMMENT 'Список сотрудников организаций, синхронизированный с 1С.'");
        }

        $permission = new Permission();
        $permission->name = 'Охрана труда: Создание заявки на формирование приказов';
        $permission->codename = "labor_safety_order_creation";
        $permission->category = 19; // Категории описаны в модели "Permission"
        $permission->save();

        $permission = new Permission();
        $permission->name = 'Охрана труда: Просмотр полного списка заявок на формирование приказов';
        $permission->codename = "labor_safety_order_list_access";
        $permission->category = 19; // Категории описаны в модели "Permission"
        $permission->save();

        $permission = new Permission();
        $permission->name = 'Охрана труда: Редактирование шаблонов приказов';
        $permission->codename = "labor_safety_order_types_editing";
        $permission->category = 19; // Категории описаны в модели "Permission"
        $permission->save();


        if (!Schema::hasTable('labor_safety_order_type_categories')) {
            Schema::create('labor_safety_order_type_categories', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('name')->unique()->comment('Значение');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_order_type_categories COMMENT 'Виды типов приказов в модуле «Охрана труда»'");

            $laborSafetyOrderTypeCategoryArray = ['Приказ о назначении ответственных с замещением',
                'Приказ о назначении списка сотрудников',
                'Приказ о назначении ответственных за производство работ повышенной опасности',
                'Приказ о назначении ответственных с замещением и назначением сотрудников',
                'Приказ о назначении ответственного за производство геодезических работ',
                'Приказ о назначении ответственного за СРО',
                'Приказ о назначении ответственного за охрану труда',
                'Приказ о назначении ответственных лиц за охрану окружающей среды',
                'Приказ о направлении работников на строительный объект в выходные и праздничные дни',
                'Приказ о назначении ответственного за приемку и контроль качества электрогазосварочных работ',
                'Доверенность',
                'Сопроводительное письмо'
            ];
            foreach ($laborSafetyOrderTypeCategoryArray as $laborSafetyOrderTypeCategoryElement) {
                $laborSafetyOrderTypeCategory = new LaborSafetyOrderTypeCategory([
                    'name' => $laborSafetyOrderTypeCategoryElement
                ]);
                $laborSafetyOrderTypeCategory->save();
            }
        }

        if (!Schema::hasTable('labor_safety_order_types')) {
            Schema::create('labor_safety_order_types', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('order_type_category_id')->unsigned()->comment('Вид типа приказа');
                $table->string('name')->unique()->comment('Наименование');
                $table->string('short_name')->unique()->comment('Краткое наименование');
                $table->string('full_name')->unique()->comment('Краткое наименование');
                $table->text('template')->comment('Шаблон');

                $table->foreign('order_type_category_id')->references('id')->on('labor_safety_order_type_categories');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_order_types COMMENT 'Типы приказов для формирования в модуле «Охрана труда»'");

            $laborSafetyOrderTypesArray = ['Ответственный за производство работ:ОПР:О назначении ответственного производителя работ на строительном объекте:1', // <p style="text-align: center;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><table><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr><tr><td><p style="text-align: justify;">&laquo;О назначении ответственного производителя работ при выполнении полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p style="text-align: left;">В связи с производственной необходимостью,</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить {responsible_employee_post} {responsible_employee_full_name} <span style="font-size: 10pt; font-family: Tahoma, sans-serif;">ответственным, за организацию и безопасное производство строительно-монтажных работ (СМР) с правом получения от Заказчика проектной документации и иной документации необходимой для производства работ в рамках &laquo;Договора строительного субподряда&raquo;, согласования производства работ.</span><span style="font-size: 10pt; font-family: 'Lucida Console';">[optional-section-start|subresponsible_employee]</span></li><li>На время отсутствия: болезни, отпуска и. т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п.1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name}<span style="font-family: 'Lucida Console'; font-size: 10pt;">[optional-section-end|subresponsible_employee]</span></li><li>Возложить на {responsible_employee_post} {responsible_employee_name_initials_after}:<ol><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">полную материальную ответственность с правом подписи на документах по приему товарно-материальных ценностей в пределах порученного участка работ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">право подписи на исполнительной документации;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">право получения актов, предписаний;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанность своевременного заказов строительной техники и материалов на строительную площадку (заранее, за 1-2 дня);</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанность по оценке достаточности ТМЦ на строительном объекте для бесперебойного производство работ; </span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">руководство строительной техникой и персоналом в зоне ответственности СМР;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанность посещать производственные совещания Заказчика;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица за допуск исправного оборудования к производству работ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанность за проведение работ в соответствии с &laquo;графиком &ndash; работ&raquo;;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности по ведению и своевременному предоставлению отчётности в пределах вверенного объекта строительства;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица на объекте за обеспечение безопасного производства работ, соблюдение требований охраны труда, промышленной и пожарной безопасности, а также производственной санитарии в пределах порученного участка работ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного руководителя работ по &laquo;Наряду-допуску&raquo; на производство работ повышенной опасности;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица, за организацию погрузочно-разгрузочных работ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица, за сохранность и исправность электроинструмента;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица по проверке и браковке инструмента;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного руководителя работ на высоте;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица на объекте, за выдачу и контроль применения сертифицированных СИЗ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица, за соблюдение трудовой дисциплины;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица по составлению плана мероприятий при аварийной ситуации и при проведении спасательных работ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица, за безопасное проведение электрогазосварочных работ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица, за обеспечение безопасного производства работ при мобилизации/демобилизации/перемещении технологического оборудования (буровые установки, подъёмные сооружения, вибропогружатели, копры и иное оборудование) на месте производства работ;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица, за обеспечение безопасного производства при монтаже/демонтаже технологического оборудования (буровые установки, подъёмные сооружения, вибропогружатели, копры и иное оборудование) на месте производства работ в соответствии с техническим описанием, инструкцией по эксплуатации завода изготовителя, ППР и технологическими картами;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица, за контроль безопасного производством работ Подрядных организаций (компаний) выполняющих работы по Договору подряду/субподряду и работниками по Гражданско-правовому договору в зоне ответственности выполняемых работ по основному договору;</span></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">обязанности ответственного лица за укомплектованностью бытовых и технических помещений, сварочных постов, техники средствами пожаротушения и аптечками.</span></li></ol></li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">Контроль, за исполнением настоящего приказа оставляю за собой.</span></li></ol><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М.Д. Исмагилов</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%; height: 111px;"><tbody><tr style="height: 76px;"><td style="width: 33%; height: 76px;"><p>С&nbsp;приказом&nbsp;ознакомлен:</p></td><td style="width: 33%; border-bottom: 1px solid black; height: 76px; vertical-align: top;">&nbsp;</td><td style="height: 76px; width: 33%;"><p style="text-align: right;">{responsible_employee_name_initials_before}</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr></tbody></table><p><span style="font-family: 'Lucida Console'; font-size: 8pt;">[optional-section-start|subresponsible_employee]</span></p><table style="width: 100%; height: 111px;"><tbody><tr style="height: 76px;"><td style="width: 33%; height: 76px;"><p>С&nbsp;приказом&nbsp;ознакомлен:</p></td><td style="width: 33%; border-bottom: 1px solid black; height: 76px; vertical-align: top;">&nbsp;</td><td style="height: 76px; width: 33%;"><p style="text-align: right;">{subresponsible_employee_name_initials_before}</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr></tbody></table><p><span style="font-size: 8pt; font-family: 'Lucida Console';">[optional-section-end|subresponsible_employee]</span></p>
                'Направление на объект:Н:О направлении работников на строительный объект:2', //<p style="text-align: center;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><table><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr><tr><td><p style="text-align: justify;">&laquo;О направлении работников на строительный объект при устройстве работ по погружению и извлечению шпунтового ограждения на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p style="text-align: left;">В связи с производственной необходимостью,</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>С 21.07.2022 г. направить на строительный объект следующих работников: {workers_list}</li><li style="text-align: left;"><span style="font-size: 10pt; font-family: Tahoma, sans-serif;">Контроль, за исполнением настоящего приказа оставляю за собой.</span></li></ol><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М.Д. Исмагилов</p></td></tr></tbody></table><p>&nbsp;</p><p>{sign_list}</p><p>&nbsp;</p>
                'Обеспечение требований охраны труда:ОТ:О назначении ответственного за обеспечение требований охраны труда на строительном объекте:1',
                'Пожарная безопасность:ПБ:О назначении ответственного за пожарную безопасность и обеспечение противопожарных мероприятий на строительном объекте:1',
                'Организация работ повышенной опасности:НД:Об организации работ повышенной опасности:3',
                'Состояние и применение ограждений:ОГ:О назначении ответственных за исправное состояние и правильное применение ограждений:1',
                'Производство погрузочно-разгрузочных работ:ПР:О назначении ответственного за производство погрузочно-разгрузочных работ:1',
                'Сохранность и исправность электроинструмента:ЭИ:О назначении ответственного за сохранность и исправность электроинструмента:1',
                'Электрохозяйство на строительном объекте:ЭХ:О назначении ответственного за электрохозяйство на строительном объекте:1',
                'Производство сварочных работ:СВ:О назначении лица, ответственного за безопасное производство сварочных работ и допуске электрогазосварщиков к работе на строительном объекте:4',
                'Эксплуатация баллонов с газами:Б-ОТ:О безопасной эксплуатации баллонов со сжатыми и сжиженными газами:4',
                'Производство работ с применением подъемных сооружений:ПС:Об организации безопасного производства работ с применением подъемных сооружений:4',
                'Осмотр съемных грузозахватных приспособлений:СГП:О назначении ответственного за осмотр съемных грузозахватных приспособлений во время эксплуатации, установки и демонтажа:1',
                'Производство геодезических работ:Г:О назначении ответственного за производство геодезических работ:5',
                'Введение режима повышенной готовности:РПГ:О введении режима повышенной готовности:1',
                'Приемка законченных работ:СРО:О назначении ответственного за приемку законченных видов и отдельных этапов работ:6',
                'Контроль по охране труда:ОТК:О назначении специалиста по охране труда:7',
                'Допуск персонала:ДП:О допуске персонала, обслуживающего подъемные сооружения на строительном объекте:2',
                'Допуск электрогазосварщиков:ДСВ:О допуске электрогазосварщиков к работе:2',
                'Подготовка, оформление и подписание исполнительной документации:ИС:О назначении ответственных лиц за подготовку, оформление и подписание исполнительной документации:4',
                'Разработка проектной документации:П:О назначении ответственного специалиста за разработку проектной документации:2',
                'Охрана окружающей среды:ЭК:О назначении ответственных лиц за охрану окружающей среды, обеспечение экологической безопасности, обращение с отходами:8',
                'Направление работников и назначение ответственных в выходные дни:Н-ВЫХ:О направлении работников на строительный объект и назначении ответственного за производство работ в выходные дни:9',
                'Приемка электрогазосварочных работ:СК:Об организации приемки и контроля качества электрогазосварочных сварочных работ:10',
                'Доверенность:Доверенность:Доверенность:11',
                'Сопроводительное письмо:Сопроводительное письмо:Сопроводительное письмо:12'];

            foreach ($laborSafetyOrderTypesArray as $laborSafetyOrderTypeElement) {
                $laborSafetyOrderType = new LaborSafetyOrderType([
                    'name' => explode(':', $laborSafetyOrderTypeElement)[0],
                    'short_name' => explode(':', $laborSafetyOrderTypeElement)[1],
                    'full_name' => explode(':', $laborSafetyOrderTypeElement)[2],
                    'order_type_category_id' => explode(':', $laborSafetyOrderTypeElement)[3],

                ]);
                $laborSafetyOrderType->save();
            }
        }

        if (!Schema::hasTable('labor_safety_request_statuses')) {
            Schema::create('labor_safety_request_statuses', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('name')->comment('Значение');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_request_statuses COMMENT 'Состояние заявок на формирование приказов в модуле «Охрана труда»'");

            $laborSafetyRequestStatusArray = ['Новая',
                'Подписание документов',
                'Отменена',
                'Завершена'
            ];

            foreach ($laborSafetyRequestStatusArray as $laborSafetyRequestStatusElement) {
                $laborSafetyRequestStatus = new LaborSafetyRequestStatus([
                    'name' => $laborSafetyRequestStatusElement
                ]);
                $laborSafetyRequestStatus->save();
            }

        }
        if (!Schema::hasTable('labor_safety_requests')) {
            Schema::create('labor_safety_requests', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');

                $table->date('order_date')->index()->comment('Дата приказа');
                $table->integer('company_id')->unsigned()->comment('ID компании');
                $table->integer('project_object_id')->unsigned()->comment('ID объекта');
                $table->integer('author_user_id')->unsigned()->comment('ID автора');
                $table->integer('implementer_user_id')->unsigned()->nullable()->comment('ID исполнителя');
                $table->bigInteger('responsible_employee_id')->unsigned()->comment('ID ответственного сотрудника');
                $table->bigInteger('sub_responsible_employee_id')->unsigned()->nullable()->comment('ID замещающего ответственного сотрудника');
                $table->integer('request_status_id')->unsigned()->comment('ID статуса заявки');
                $table->text('comment')->comment('Комментарий');

                $table->foreign('company_id')->references('id')->on('companies');
                $table->foreign('project_object_id')->references('id')->on('project_objects');
                $table->foreign('author_user_id')->references('id')->on('users');
                $table->foreign('implementer_user_id', 'l_s_r_implementer_user_id_foreign')->references('id')->on('users');
                $table->foreign('responsible_employee_id', 'l_s_r_resp_employee_id_foreign')->references('id')->on('employees');
                $table->foreign('sub_responsible_employee_id', 'l_s_r_sub_resp_employee_id_foreign')->references('id')->on('employees');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_requests COMMENT 'Заявки на формирование приказов в модуле «Охрана труда»'");
        }

        if (!Schema::hasTable('labor_safety_request_orders')) {
            Schema::create('labor_safety_request_orders', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('order_type_id')->unsigned()->comment('ID типа приказа');

                $table->text('generated_html')->comment('Сгенерированный приказ в html');

                $table->foreign('order_type_id')->references('id')->on('labor_safety_order_types');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_request_orders COMMENT 'Приказы для заявок на формирование приказов в модуле «Охрана труда»'");
        }

        if (!Schema::hasTable('labor_safety_request_workers')) {
            Schema::create('labor_safety_request_workers', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('request_id')->unsigned()->comment('ID Заявки');
                $table->bigInteger('worker_employee_id')->unsigned()->comment('ID сотрудника');

                $table->foreign('request_id')->references('id')->on('labor_safety_requests');
                $table->foreign('worker_employee_id', 'l_s_r_worker_employee_id_foreign')->references('id')->on('employees');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_request_workers COMMENT 'Список сотрудников (рабочих), для которых необходимо сформировать приказы в модуле «Охрана труда»'");
        }

        if (!Schema::hasTable('labor_safety_order_workers')) {
            Schema::create('labor_safety_order_workers', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('order_id')->unsigned()->comment('ID приказа');
                $table->integer('requests_worker_id')->unsigned()->comment('ID записи со ссылкой на сотрудника, сформированному при подаче заявки');

                $table->foreign('order_id')->references('id')->on('labor_safety_request_orders');
                $table->foreign('requests_worker_id', 'order_worker_employee_id_foreign')->references('id')->on('labor_safety_request_workers');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_order_workers COMMENT 'Список сотрудников (рабочих), которые участвуют при формировании приказов в модуле «Охрана труда»'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = Permission::where('codename', 'labor_safety_order_creation')->first();
        if (isset($permission)) {
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        $permission = Permission::where('codename', 'labor_safety_order_list_access')->first();
        if (isset($permission)) {
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        $permission = Permission::where('codename', 'labor_safety_generate_documents_access')->first();
        if (isset($permission)) {
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        $permission = Permission::where('codename', 'labor_safety_order_types_editing')->first();
        if (isset($permission)) {
            UserPermission::where('permission_id', $permission->id)->forceDelete();
            $permission->forceDelete();
        }

        /*        Schema::dropIfExists('employees');
                Schema::dropIfExists('employees_1c_posts');
                Schema::dropIfExists('employees_1c_subdivisions');
                Schema::dropIfExists('employees_report_groups');*/

        /*Schema::dropIfExists('labor_safety_order_workers');
        Schema::dropIfExists('labor_safety_request_workers');
        Schema::dropIfExists('labor_safety_request_orders');
        Schema::dropIfExists('labor_safety_requests');
        /*Schema::dropIfExists('labor_safety_request_statuses');
              Schema::dropIfExists('labor_safety_order_types');
               Schema::dropIfExists('labor_safety_order_type_categories');

               Schema::dropIfExists('company_report_templates');
               Schema::dropIfExists('company_report_template_types');
               Schema::dropIfExists('companies');*/

        /*Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('individual_1c_uid');
        });*/
    }
}

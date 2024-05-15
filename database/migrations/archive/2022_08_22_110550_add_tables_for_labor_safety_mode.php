<?php

use App\Models\Company\Company;
use App\Models\Company\CompanyReportTemplate;
use App\Models\Company\CompanyReportTemplateType;
use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyOrderTypeCategory;
use App\Models\LaborSafety\LaborSafetyRequestStatus;
use App\Models\LaborSafety\LaborSafetyWorkerType;
use App\Models\Permission;
use App\Models\Timesheet\EmployeesReportGroup;
use App\Models\UserPermission;
use Database\Seeders\employeePostsSeeder;
use Database\Seeders\employeesSeeder;
use Database\Seeders\employeeSubdivisionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('users', 'inn')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('INN')->nullable()->unique()->comment('ИНН пользователя');
            });
        }

        if (! Schema::hasColumn('users', 'gender')) {
            Schema::table('users', function (Blueprint $table) {
                $table->char('gender')->nullable()->comment('Пол пользователя (M - мужской, F - женский)');
            });
        }

        if (! Schema::hasTable('employees_report_groups')) {
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
                    'name' => $reportGroupElement,
                ]);
                $reportGroup->save();
            }
        }

        if (! Schema::hasTable('companies')) {
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
                'company_1c_uid' => '4be56ff8-3a11-11e2-a4d2-0019d11ffeaf',
                'name' => 'ООО «СК ГОРОД»',
                'full_name' => 'Общество с ограниченной ответственностью «СК ГОРОД»',
                'legal_address' => '196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, лит. А, каб. 406',
                'actual_address' => '196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, лит. А, каб. 406',
                'phone' => '+7 (812) 335-90-90',
                'ogrn' => '1107847027045',
                'inn' => '7807348494',
                'web_site' => 'www.sk-gorod.com',
                'email' => 'info@sk-gorod.com',
            ]);
            $company->save();

            $company = new Company([
                'company_1c_uid' => 'a5f0bc19-9bf1-11e9-812f-00155d630402',
                'name' => 'ООО «ГОРОД»',
                'full_name' => 'Общество с ограниченной ответственностью «ГОРОД»',
                'legal_address' => '196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, лит. А, помещ. 56-н, каб. 406',
                'actual_address' => '196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, лит. А, каб. 406',
                'phone' => '+7 (812) 335-90-90',
                'ogrn' => '1167847146917',
                'inn' => '7807115228',
                'web_site' => 'www.sk-gorod.com',
                'email' => 'info@sk-gorod.com',
            ]);
            $company->save();

            $company = new Company([
                'company_1c_uid' => '2803b065-65a3-11e5-84a7-50465d8f7441',
                'name' => 'ООО «СТРОЙМАСТЕР»',
                'full_name' => 'Общество с ограниченной ответственностью «СТРОЙМАСТЕР»',
                'legal_address' => '196128, г. Санкт-Петербург, Кузнецовская ул., д. 19, лит. А, помещ. 12Н (№18)',
                'actual_address' => '196128, г. Санкт-Петербург, Кузнецовская ул., д. 19, лит. А, помещ. 12Н (№18)',
                'phone' => '+7 (812) 303-90-53',
                'ogrn' => '1147847349165',
                'inn' => '7842528806',
                'web_site' => '',
                'email' => 'stroymaster9@yandex.ru',
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
                'email' => 'rentmaster10@yandex.ru',
            ]);
            $company->save();
        }

        if (! Schema::hasTable('company_report_template_types')) {
            Schema::create('company_report_template_types', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('name')->unsigned()->comment('Значение');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE company_report_template_types COMMENT 'Типы шаблонов для бланков компаний'");

            $reportTemplateTypeArray = ['header', 'footer'];
            foreach ($reportTemplateTypeArray as $reportTemplateTypeElement) {
                $reportTemplateType = new CompanyReportTemplateType([
                    'name' => $reportTemplateTypeElement,
                ]);
                $reportTemplateType->save();
            }
        }

        if (! Schema::hasTable('company_report_templates')) {
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

            $companiesReportTemplateArray = [
                '1|1|<table style="width: 100%; height: 120px; border-collapse: collapse;"><tbody><tr><td style="width: 50%;"><img src="https://erp.sk-gorod.com/img/sk-gorod-logo.png" width="286" alt="ООО «СК ГОРОД»"/></td><td style="width: 50%;"><p style="text-align: right;">{company_name}</p><p style="text-align: right;">{company_legal_address}</p><p style="text-align: right;">Тел.: {company_phone}</p><p style="text-align: right;">{company_web_site}</p><p style="text-align: right;">{company_email}</p></td></tr></tbody></table>',
            ];

            foreach ($companiesReportTemplateArray as $companiesReportTemplateElement) {
                $companiesReportTemplate = new CompanyReportTemplate([
                    'company_id' => explode('|', $companiesReportTemplateElement)[0],
                    'template_type' => explode('|', $companiesReportTemplateElement)[1],
                    'template' => explode('|', $companiesReportTemplateElement)[2],
                ]);
                $companiesReportTemplate->save();
            }
        }

        if (! Schema::hasTable('employees_1c_subdivisions')) {
            Schema::create('employees_1c_subdivisions', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('subdivision_parent_id')->nullable()->unsigned()->comment('Уникальный идентификатор');
                $table->string('name')->comment('Наименование должности');
                $table->string('subdivision_1c_uid')->comment('Уникальный идентификатор 1С');
                $table->integer('company_id')->unsigned()->comment('ID организации');

                $table->foreign('company_id')->references('id')->on('companies');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE employees_1c_subdivisions COMMENT 'Список подразделений, синхронизированных с 1С'");
        }

        if (! Schema::hasTable('employees_1c_posts')) {
            Schema::create('employees_1c_posts', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('name')->comment('Наименование должности');
                $table->string('declination_format')->comment('Формат склонения');
                $table->string('post_1c_uid')->comment('Наименование отчетной группы');
                $table->integer('company_id')->unsigned()->comment('ID организации');

                $table->foreign('company_id')->references('id')->on('companies');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE employees_1c_posts COMMENT 'Должности сотрудников, синхронизированных c 1С'");
        }

        if (! Schema::hasTable('employees_1c_post_inflections')) {
            Schema::create('employees_1c_post_inflections', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('post_id')->unsigned()->comment('Id должности');
                $table->string('nominative')->nullable()->comment('Именительный падеж');
                $table->string('genitive')->nullable()->comment('Родительный падеж');
                $table->string('dative')->nullable()->comment('Дательный падеж');
                $table->string('accusative')->nullable()->comment('Винительный падеж');
                $table->string('ablative')->nullable()->comment('Творительный падеж');
                $table->string('prepositional')->nullable()->comment('Предложный падеж');

                $table->foreign('post_id')->references('id')->on('employees_1c_posts');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE employees_1c_post_inflections COMMENT 'Склонения должностей'");
        }

        if (! Schema::hasTable('employees')) {
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

                $table->integer('report_group_id')->unsigned()->nullable()->comment('Отчетная группа');

                $table->string('temp_last_name')->comment('Фамилия сотрудника (для первичной синхронизации сотрудников)');
                $table->string('temp_first_name')->comment('Имя сотрудника (для первичной синхронизации сотрудников)');
                $table->string('temp_patronymic')->comment('Отчество сотрудника (для первичной синхронизации сотрудников)');

                $table->date('temp_birthday')->comment('Дата рождения сотрудника (для первичной синхронизации сотрудников)');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('report_group_id')->references('id')->on('employees_report_groups');
                $table->foreign('company_id')->references('id')->on('companies');
                $table->foreign('employee_1c_subdivision_id')->references('id')->on('employees_1c_subdivisions');
            });
            DB::statement("ALTER TABLE employees COMMENT 'Список сотрудников организаций, синхронизированных с 1С.'");
        }

        if (! Schema::hasTable('employee_name_inflections')) {
            Schema::create('employee_name_inflections', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->bigInteger('employee_id')->unsigned()->comment('Id сотрудника');
                $table->string('nominative')->nullable()->comment('Именительный падеж');
                $table->string('genitive')->nullable()->comment('Родительный падеж');
                $table->string('dative')->nullable()->comment('Дательный падеж');
                $table->string('accusative')->nullable()->comment('Винительный падеж');
                $table->string('ablative')->nullable()->comment('Творительный падеж');
                $table->string('prepositional')->nullable()->comment('Предложный падеж');

                $table->foreign('employee_id')->references('id')->on('employees');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE employee_name_inflections COMMENT 'Склонения имен сотрудников'");
        }

        $permission = new Permission();
        $permission->name = 'Охрана труда: Создание заявки на формирование приказов';
        $permission->codename = 'labor_safety_order_creation';
        $permission->category = 19; // Категории описаны в модели "Permission"
        $permission->save();

        $permission = new Permission();
        $permission->name = 'Охрана труда: Просмотр полного списка заявок на формирование приказов';
        $permission->codename = 'labor_safety_order_list_access';
        $permission->category = 19; // Категории описаны в модели "Permission"
        $permission->save();

        $permission = new Permission();
        $permission->name = 'Охрана труда: Редактирование шаблонов приказов';
        $permission->codename = 'labor_safety_order_types_editing';
        $permission->category = 19; // Категории описаны в модели "Permission"
        $permission->save();

        if (! Schema::hasTable('labor_safety_order_type_categories')) {
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
                'Сопроводительное письмо',
            ];
            foreach ($laborSafetyOrderTypeCategoryArray as $laborSafetyOrderTypeCategoryElement) {
                $laborSafetyOrderTypeCategory = new LaborSafetyOrderTypeCategory([
                    'name' => $laborSafetyOrderTypeCategoryElement,
                ]);
                $laborSafetyOrderTypeCategory->save();
            }
        }

        if (! Schema::hasTable('labor_safety_order_types')) {
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

            $laborSafetyOrderTypesArray = ['Ответственный за производство работ‡ОПР‡О назначении ответственного производителя работ на строительном объекте‡1‡<p>&nbsp;</p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного производителя работ при выполнении полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td style="width: 9.67188px;"><p>&nbsp;</p></td></tr></tbody></table><p>&nbsp;</p><p><br /><br /></p><p style="text-align: left;">В связи с производственной необходимостью,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить {responsible_employee_post} {responsible_employee_full_name} ответственным, за организацию и безопасное производство строительно-монтажных работ (СМР) с правом получения от Заказчика проектной документации и иной документации необходимой для производства работ в рамках &laquo;Договора строительного субподряда&raquo;, согласования производства работ.[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и. т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п.1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name}[optional-section-end|subresponsible_employee]</li><li>Возложить на {responsible_employee_post} {responsible_employee_name_initials_after}:<ol><li style="text-align: left;">Полную материальную ответственность с правом подписи на документах по приему товарно-материальных ценностей в пределах порученного участка работ;</li><li style="text-align: left;">Право подписи на исполнительной документации;</li><li style="text-align: left;">Право получения актов, предписаний;</li><li style="text-align: left;">Обязанность своевременного заказа строительной техники и материалов на строительную площадку (заранее, за 1-2 дня);</li><li style="text-align: left;">Обязанность по оценке достаточности ТМЦ на строительном объекте для бесперебойного производство работ;</li><li style="text-align: left;">Руководство строительной техникой и персоналом в зоне ответственности СМР;</li><li style="text-align: left;">Обязанность посещать производственные совещания Заказчика;</li><li style="text-align: left;">Обязанности ответственного лица за допуск исправного оборудования к производству работ;</li><li style="text-align: left;">Обязанность за проведение работ в соответствии с &laquo;графиком &ndash;работ&raquo;;</li><li style="text-align: left;">Обязанности по ведению и своевременному предоставлению отчётности в пределах вверенного объекта строительства;</li><li style="text-align: left;">Обязанности ответственного лица на объекте за обеспечение безопасного производства работ, соблюдение требований охраны труда, промышленной и пожарной безопасности, а также производственной санитарии в пределах порученного участка работ;</li><li style="text-align: left;">Обязанности ответственного руководителя работ по &laquo;Наряду-допуску&raquo; на производство работ повышенной опасности;</li><li style="text-align: left;">Обязанности ответственного лица, за организацию погрузочно-разгрузочных работ</li><li style="text-align: left;">Обязанности ответственного лица, за сохранность и исправность электроинструмента</li><li style="text-align: left;">Обязанности ответственного лица по проверке и браковке инструмента;</li><li style="text-align: left;">Обязанности ответственного руководителя работ на высоте;</li><li style="text-align: left;">Обязанности ответственного лица на объекте, за выдачу и контроль применения сертифицированных СИЗ;</li><li style="text-align: left;">Обязанности ответственного лица, за соблюдение трудовой дисциплины;</li><li style="text-align: left;">Обязанности ответственного лица по составлению плана мероприятий при аварийной ситуации и при проведении спасательных работ;</li><li style="text-align: left;">Обязанности ответственного лица, за безопасное проведение электрогазосварочных работ;</li><li style="text-align: left;">Обязанности ответственного лица, за обеспечение безопасного производства работ при мобилизации/демобилизации/перемещении технологического оборудования (буровые установки, подъёмные сооружения, вибропогружатели, копры и иное оборудование) на месте производства работ;</li><li style="text-align: left;">Обязанности ответственного лица, за обеспечение безопасного производства при монтаже/демонтаже технологического оборудования (буровые установки, подъёмные сооружения, вибропогружатели, копры и иное оборудование) на месте производства работ в соответствии с техническим описанием, инструкцией по эксплуатации завода изготовителя, ППР и технологическими картами;</li><li style="text-align: left;">Обязанности ответственного лица, за контроль безопасного производства работ Подрядных организаций (компаний) выполняющих работы по Договору подряду/субподряду и работниками по Гражданско-правовому договору в зоне ответственности выполняемых работ по основному договору;</li><li style="text-align: left;">Обязанности ответственного лица за укомплектованностью бытовых и технических помещений, сварочных постов, техники средствами пожаротушения и аптечками;</li></ol></li><li style="text-align: left;">Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Направление на объект‡Н‡О направлении работников на строительный объект‡2‡<p><br /></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О направлении работников на строительный объект при устройстве работ по погружению и извлечению шпунтового ограждения на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p style="text-align: left;">В связи с производственной необходимостью,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>С {order_date} г. направить на строительный объект следующих работников: {workers_list}</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><br/><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Обеспечение требований охраны труда‡ОТ‡О назначении ответственного за обеспечение требований охраны труда на строительном объекте‡1‡<p><br /></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за обеспечение требований охраны труда при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью и в целях обеспечения требований охраны труда на строительном объекте, в соответствии с требованиями СНиП 12-03-2001 &laquo;Безопасность труда в строительстве Часть 1. Общие требования&raquo;, введенными в действие Постановлением Госстроя РФ от 23.07.2001 г. № 80, Приказом Минтруда России от 11.12.2020 г. № 883н &laquo;Об утверждении Правил по охране труда при строительстве, реконструкции и ремонте&raquo; и Приказом Минздрава России от 15.12.2020 г. № 1331н &laquo;Об утверждении требований к комплектации медицинскими изделиями аптечки для оказания первой помощи работникам&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Ответственными за обеспечение требований охраны труда на строительном объекте назначить {responsible_employee_post} {responsible_employee_full_name};[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и. т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п.1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name}[optional-section-end|subresponsible_employee]</li><li>На должностное лицо, ответственное за организацию работ по охране труда на производственном участке строительства, возложить:<ol><li>Проведение инструктажа на рабочем месте по охране труда:<ol><li>Первичного инструктажа на рабочем месте до начала самостоятельной работы;</li><li>Повторного инструктажа на рабочем месте не реже одного раза в три месяца (дата проведения первый рабочий день начала квартала);</li><li>Внепланового инструктажа по решению работодателя (при перемещении работника на другой строительный объект);</li></ol></li><li>Проведение вводного инструктажа для работников подрядных организаций и работающих по гражданско-правовому договору;</li><li>Ведение журнала инструктажей на рабочем месте;</li><li>Обеспечение подчинённых работников инструкциями по охране труда для профессий и видов работ;</li><li>Организацию проведения стажировок на рабочем месте;</li><li>Допуск подчиненных сотрудников к выполнению своих должностных обязанностей после проведения стажировки и инструктажей;</li><li>Ежедневную проверку перед началом работы состояния рабочих мест, в том числе исправности оборудования и оргтехники, с информированием о нарушениях, которые не могут быть устранены собственными силами, соответствующих служб {company_name}, и допуск к работе подчиненных сотрудников после полного устранения недостатков;</li><li>Осуществление контроля за соблюдением подчиненными мер безопасности, определенных в инструкциях по охране труда и других локальных нормативных документах по охране труда, разработанных в {company_name};</li><li>Постоянное выявление опасностей, оценку профессиональных рисков и снижение их уровней;</li><li>Обеспечение соблюдения сотрудниками трудовой дисциплины и правил трудового распорядка на строительном объекте;</li><li>Своевременное информирование руководителей {company_name} и специалистов отдела охраны труда о несчастных случаях с подчиненными работниками;</li><li>Участие в расследовании несчастных случаев;</li><li>Осуществление учета и рассмотрение обстоятельств причин, которые привели к получению работниками микротравм;</li><li>Предупреждение доступа на рабочие места подчиненных сотрудников при их неудовлетворительном состоянии здоровья, в том числе в состоянии алкогольного (наркотического) опьянения;</li><li>Обеспечение безопасных условий труда на рабочих местах;</li><li>Обеспечение правил по охране труда при размещении, монтаже, техническом обслуживании и ремонте технологического оборудования;</li><li>Обеспечение санитарных и экологических требований;</li><li>Обеспечение наличия аптечек первой помощи на вверенном участке работ;</li><li>Ответственность за выдачу сертифицированных СИЗ и контроль их применения;</li><li>Обязанность по допуску к производству работ сотрудников прошедших обязательные предварительные и периодические медосмотры;</li></ol></li><li>Ответственным производителям работ руководствоваться своими должностными инструкциями, действующими локальными нормативно-правовыми актами, регламентами, указаниями контролирующих органов;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><br/><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Пожарная безопасность‡ПБ‡О назначении ответственного за пожарную безопасность и обеспечение противопожарных мероприятий на строительном объекте‡1‡<p><br /></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за пожарную безопасность и обеспечении противопожарных мероприятий при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью и в целях обеспечения пожарной безопасности на строительном объекте, на основании требований Федерального закона от 21.12.1994 г. № 69-ФЗ &laquo;О пожарной безопасности&raquo; и Постановления Правительства РФ от 16.09.2020 г. № 1479 &laquo;Об утверждении Правил противопожарного режима в Российской Федерации&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить {responsible_employee_post} {responsible_employee_full_name} ответственным лицом на строительном объекте за:<ol><li>Организацию контроля исполнения требований пожарной безопасности;</li><li>Поддержание на объекте установленного противопожарного режима на вверенном участке работ и закрепленных бытовых и инструментальных помещений;</li><li>Противопожарное состояние помещений, эксплуатацию первичных средств пожаротушения;</li><li>Учет и выдачу первичных средств пожаротушения (огнетушителей, автоматических установок пожарной сигнализации, покрывал для изоляции очага возгорания) с ведением Журнала эксплуатации систем противопожарной защиты;</li><li>Безопасное проведение пожароопасных (огневых) работ;</li><li>Проведение вводного инструктажа для работников подрядных организаций, работающих по гражданско-правовому договору и иными работниками по решению руководителя;</li><li>Проведение противопожарных инструктажей на рабочем месте:<ol><li>Первичного инструктажа со всеми прошедшими вводный противопожарный инструктаж;</li><li>Повторного инструктажа не реже одного раза в полгода;</li><li>Внепланового инструктажа по решению руководителя организации;</li><li>Целевого инструктажа, перед выполнением огневых работ и других пожароопасных и пожаровзрывоопасных работ, на которые оформляется наряд-допуск;</li></ol></li></ol>[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и. т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п.1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name}[optional-section-end|subresponsible_employee]</li><li>Работу по обеспечению пожарной безопасности проводить в соответствии с действующими в Российской Федерации законодательными нормативно-правовыми актами, действующими локальными нормативно-правовыми актами, настоящим приказом, регламентами и указаниями контролирующих органов:<ol><li>На дверях бытовых помещений и инструментальных контейнеров вывесить таблички установленной формы с указанием фамилии, имени, отчества ответственного за пожарную безопасность, номера телефона вызова пожарной охраны и обозначением категории помещений по пожарной безопасности;</li><li>Установить систематический контроль выполнения работниками правил и инструкций, определяющих требования пожарной безопасности. Запретить курение табака, применение открытого огня и пользование электронагревательными приборами в местах, не оборудованных для этой цели;</li><li>Установить систематический контроль за сохранностью и готовностью к действию первичных средств пожаротушения и пожарного инвентаря на объекте;</li></ol></li><li>Ответственным лицам при проведении пожароопасных (огневых) работ на объекте руководствоваться ФЗ от 21.12.1994 г. № 69-ФЗ &laquo;О пожарной безопасности&raquo; и Постановлением Правительства РФ от 16.09.2020 г. № 1479 &laquo;Об утверждении Правил противопожарного режима в Российской Федерации&raquo;:<ol><li>Оформить &laquo;наряд-допуск&raquo; на проведение пожароопасных (огневых) работ;</li><li>При проведении пожароопасных (огневых) работ на объекте провести целевой инструктаж;</li><li>Подготовить место проведения огневых работ с обеспечением первичных средств пожаротушения;</li></ol></li><li>Противопожарные инструктажи на рабочем месте проводить в соответствии с инструкцией &laquo;О мерах пожарной безопасности&raquo; и с оформлением в журнале учета инструктажей по пожарной безопасности;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><br/><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Организация работ повышенной опасности‡НД‡Об организации работ повышенной опасности‡3‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного производителя работ при выполнении полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью и в целях обеспечения требований охраны труда на строительном объекте, в соответствии с требованиями СНиП 12-03-2001 &laquo;Безопасность труда в строительстве Часть 1. Общие требования&raquo;, введенных в действие Постановлением Госстроя РФ от 23.07.2001 г. № 80, Приказом Минтруда России от 11.12.2020 г. № 883н &laquo;Об утверждении Правил по охране труда при строительстве, реконструкции и ремонте&raquo;, Приказом №833н от 27.11.2020 г. &laquo;Об утверждении правил по охране труда при размещении, монтаже, техническом обслуживании и ремонте технологического оборудования&raquo; и инструкцией &laquo;По организации и производству работ повышенной опасности&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственными лицами на строительном объекте, за выдачу &laquo;наряда-допуска&raquo; при производстве работ повышенной опасности на выполнение которых необходимо выдавать &laquo;наряд-допуск&raquo;:<br />{object_responsible_users}</li><li>Ответственном лицам за выдачу &laquo;наряда-допуска&raquo;:<ol><li>Оформлять &laquo;наряд-допуск&raquo; на работы повышенной опасности по установленной форме;</li><li>Проводить инструктаж с непосредственным руководителем работ по &laquo;наряду-допуску&raquo;;</li><li>Регистрировать выдачу &laquo;наряда допуска&raquo; в журнале выдачи &laquo;наряда-допуска&raquo;;</li><li>Обеспечить хранение &laquo;нарядов-допусков&raquo; по закрытым работам и журнала выдачи &laquo;наряда-допуска&raquo;;</li></ol></li><li>Назначить ответственным руководителем работ (с совмещением обязанностей ответственного производителя работ) по &laquo;наряду-допуску&raquo; {responsible_employee_post} {responsible_employee_full_name}:<ol><li>Ответственному руководителю работ не допускать персонал к выполнению работ повышенной опасности без необходимой спецодежды и инвентаря;</li><li>Осуществлять контроль за предусмотренными &laquo;нарядом-допуском&raquo; организационных, технических и других мер безопасности;</li><li>Ответственному руководителю работ руководствоваться своими должностными инструкциями, действующими локальными нормативно-правовыми актами, регламентами и указаниями контролирующих органов власти;</li><li>Ответственному руководителю работ проводить инструктаж со всеми членами бригады, участвующими в работе по &laquo;наряду-допуску&raquo;;</li><li>Проверять готовность рабочих мест по указанным в &laquo;наряде-допуске&raquo; мерам безопасности;</li></ol>[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и. т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п.3 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};[optional-section-end|subresponsible_employee]</li><li>&laquo;Наряд-допуск&raquo; выдавать в 2-х экземплярах (1-й находится у лица, выдавшего наряд, 2-й у ответственного руководителя работ);</li><li><strong>Перечень работ, связанных с повышенной опасностью, выполняемых с оформлением наряда-допуска:<br /></strong>&mdash; Монтаж, демонтаж шпунтового ограждения;<br />&mdash; Буровые работы;<br />&mdash; Работы по погружению свай (методом забивки, методом вдавливания);<br />&mdash; Земляные работы в зоне расположения подземных энергетических сетей, газонефтепроводов и других аналогичных подземных коммуникаций и объектов;<br />&mdash; Рытье котлованов, траншей глубиной более 1,5 м и производство работ в них;<br />&mdash; Строительные, монтажные, ремонтные и другие работы, выполняемые в условиях действующих производств одного подразделения организации силами другого подразделения или подрядной организацией при соприкосновении или наложении их производственных деятельностей, — так называемые совмещенные работы;<br />&mdash; Ремонтные, строительные и монтажные работы на высоте более 1,8 м от пола без инвентарных лесов и подмостей;<br />&mdash; Работы с высоким риском падения работника с высоты, а также работы на высоте без применения средств подмащивания, выполняемые на высоте 5 м и более; работы, выполняемые на площадках на расстоянии менее 2 м от не огражденных (при отсутствии защитных ограждений) перепадов по высоте более 5 м либо при высоте ограждений, составляющей менее 1,1 м;<br />&mdash; Работы по подъему, спуску и перемещению тяжеловесных и крупногабаритных грузов при отсутствии машин соответствующей грузоподъемности;<br />&mdash; Ремонт крупногабаритного оборудования высотой 1,8 м и более;<br />&mdash; Ремонтные, строительные и монтажные работы, обслуживание светильников и другие виды работ, выполняемых с галерей мостовых кранов;<br />&mdash; Работы по окраске грузоподъемных кранов и очистке их от пыли, снега и другие аналогичные работы;<br />&mdash; Работы по обслуживанию электроустановок на кабельных или воздушных линиях электропередачи;<br />&mdash; Работы краном вблизи воздушных линий электропередачи;<br />&mdash; Проведение огневых работ в пожаро-взрывоопасных помещениях;<br />&mdash; Работы повышенной опасности на высоте;<br />&mdash; Работы на высоте, выполняемые на нестационарных рабочих местах;<br />&mdash; Работы на высоте в охранных зонах сооружений или коммуникаций;<br />&mdash; Работы, выполняемые на высоте без защитных ограждений, с применением удерживающих, позиционирующих, страховочных систем и/или систем канатного доступа;<br />&mdash; Работы в замкнутых объемах, а так же работы, связанные со спуском работников в колодцы, камеры, резервуары, технические подполья (т.е. работы в ограниченном пространстве);<br />&mdash; Работы с применением грузоподъемных кранов и других строительных машин в охранных зонах воздушных линий электропередачи, газонефтепродуктопроводов, складов легковоспламеняющихся или горючих жидкостей, горючих или сжиженных газов;<br />&mdash; Осуществление текущего ремонта, демонтажа оборудования, а также производство ремонтных или каких-либо строительно-монтажных работ при наличии опасных факторов действующего опасного производственного объекта;<br />&mdash; Работы на участках, где имеется или может возникнуть опасность, связанная с выполнением опасных работ на смежных участках;<br />&mdash; Работы в непосредственной близости от полотна или проезжей части эксплуатируемых автомобильных и железных дорог;<br />&mdash; Монтаж оборудования, трубопроводов и воздухопроводов в охранных зонах воздушных линий электропередачи, газопроводов, а также складов легковоспламеняющихся или горючих жидкостей, горючих или сжиженных газов;<br />&mdash; Электросварочные работы и газосварочные работы;<br />&mdash; Электросварочные и газосварочные работы, выполняемые на высоте более 5 м;<br />&mdash; Электросварочные и газосварочные работы, выполняемые в местах, опасных в отношении поражения электрическим током (объекты электроэнергетики и атомной энергетики) и с ограниченным доступом посещения (помещения, где применяются и хранятся сильнодействующие ядовитые, химические и радиоактивные вещества);<br />&mdash; Монтаж и демонтаж технологического оборудования;<br />&mdash; Производство монтажных и ремонтных работ в непосредственной близости от открытых движущихся частей работающего оборудования, а также вблизи электрических проводов, находящихся под напряжением;<br />&mdash; Монтажные и ремонтные работы на высоте более 1,8 м от уровня пола без применения инвентарных лесов и подмостей;<br />&mdash; Электросварочные и газосварочные работы в закрытых резервуарах, в цистернах, в ямах, в колодцах, в тоннелях;<br />&mdash; Проведение огневых работ в пожароопасных и взрывоопасных помещениях;<br />&mdash; Ремонт грузоподъемных машин (кроме колесных и гусеничных самоходных), крановых тележек, подкрановых путей;<br />&mdash; Ремонт вращающихся механизмов;</li><li>Работы в местах, опасных в отношении загазованности, взрывоопасности, поражения электрическим током и с ограниченным доступом посещения при выполнении работ в охранных зонах сооружений или коммуникаций &laquo;наряд-допуск&raquo; должен выдаваться при наличии письменного разрешения организации-владельца этого сооружения или коммуникации;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Состояние и применение ограждений‡ОГ‡О назначении ответственных за исправное состояние и правильное применение ограждений‡1‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за исправное состояние и правильное применение ограждений во время эксплуатации, установки и демонтажа при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В целях обеспечения требований безопасности на строительном объекте, в соответствии с требованиями п. 9.2. ГОСТ Р 12.3.053-2020 &laquo;Система стандартов безопасности труда. Строительство. Ограждения предохранительные временные. Общие технические условия&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Ответственным за исправное состояние и правильное применение ограждений во время эксплуатации, установки и демонтажа на строительном объекте назначить&nbsp; {responsible_employee_post} {responsible_employee_full_name};[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п.1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};[optional-section-end|subresponsible_employee]</li><li>Ответственному за исправное состояние и правильное применение ограждений во время эксплуатации, установки и демонтажа обеспечить:</li><ol><li>Проведение периодического осмотра ограждений, который состоит в визуальном осмотре (проверке) исправного состояния сборочных единиц и элементов ограждения;</li><li>Замену либо ремонт элементов ограждений с обнаруженными неисправностями;</li><li>Проведение огневых работ на расстоянии не менее 1,5 м от синтетических сеток;</li><li>Установку и снятие ограждений в технологической последовательности, обеспечивающей безопасность выполнения строительно-монтажных работ;</li><li>Протяженность ограждаемого участка в соответствии с технологической картой;</li><li>соблюдение требований ГОСТ Р 12.3.053-2020 и инструкции по эксплуатации ограждений;</li></ol><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Производство погрузочно-разгрузочных работ‡ПР‡О назначении ответственного за производство погрузочно-разгрузочных работ‡1‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за производство погрузочно-разгрузочных работ при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В соответствии с требованиями Приказа Минтруда России от 28.10.2020 г. № 753н &laquo;Об утверждении Правил по охране труда при погрузочно-разгрузочных работах и размещении грузов&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Ответственным за производство погрузочно-разгрузочных работ без применения подъемных сооружений на строительном объекте назначить {responsible_employee_post} {responsible_employee_full_name};</li>[optional-section-start|subresponsible_employee]<li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п. 1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};</li>[optional-section-end|subresponsible_employee]<li>Ответственному за производство погрузочно-разгрузочных работ:</li><ol><li>Организовать работы, связанные с погрузкой, разгрузкой и размещением грузов на строительном объекте в соответствии с требованиями &laquo;Правил по охране труда при погрузочно-разгрузочных работах и размещении грузов&raquo;;</li><li>Допускать к производству погрузочно-разгрузочных работ только работников, прошедших обучение безопасным методам и приемам выполнения этих работ и, в установленные сроки, инструктажи на рабочем месте;</li><li>Перед началом работы организовать охранную зону в местах производства работ;</li><li>Проверить внешним осмотром исправность грузоподъемных механизмов, такелажного и другого погрузочно-разгрузочного инвентаря;</li><li>Следить за тем, чтобы выбор способов погрузки, разгрузки, перемещения грузов соответствовал требованиям безопасного производства работ;</li><li>При возникновении аварийных ситуаций или опасности травмирования работников немедленно прекратить работы и принять меры для устранения опасности;</li></ol><li>Персоналу, допущенному к производству погрузочно-разгрузочных работ обеспечить выполнение требований инструкции &laquo;Инструкция по охране труда при производстве погрузочно-разгрузочных работ без применения грузоподъемных механизмов&raquo;, пожарной безопасности и других действующих нормативных технических документов при производстве погрузочно-разгрузочных работ.</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Сохранность и исправность электроинструмента‡ЭИ‡О назначении ответственного за сохранность и исправность электроинструмента‡1‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за сохранность и исправность электроинструмента при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью и в целях исполнения требований приказа Минтруда России от 27.11.2020 г. № 835н &laquo;Об утверждении Правил по охране труда при работе с инструментом и приспособлениями&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным за поддержание в исправном состоянии, проведение периодических испытаний и проверок ручных электрических машин, переносных электроинструментов и светильников, вспомогательного оборудования на строительном объекте {responsible_employee_post} {responsible_employee_full_name};[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п. 1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name}; [optional-section-end|subresponsible_employee]</li><li>Результаты осмотров испытаний и ремонтов заносить в &laquo;Журнал учета осмотров, испытаний и ремонтов&raquo; произвольной формы;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Электрохозяйство на строительном объекте‡ЭХ‡О назначении ответственного за электрохозяйство на строительном объекте‡1‡<br/>',
                'Производство сварочных работ‡СВ‡О назначении лица, ответственного за безопасное производство сварочных работ и допуске электрогазосварщиков к работе на строительном объекте‡4‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении лица, ответственного за безопасное производство сварочных работ и допуске электрогазосварщиков к работе при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В соответствии с требованиями Постановления Правительства РФ от 16.09.2020 г. № 1479 &laquo;Об утверждении Правил противопожарного режима в Российской Федерации&raquo;, Приказа Минтруда России от 11.12.2020 г. № 884н &laquo;Об утверждении Правил по охране труда при выполнении электросварочных и газосварочных работ&raquo; и ГОСТа 12.3.003-86 &laquo;ССБТ. Работы электросварочные. Требования безопасности&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Ответственным за безопасное производство сварочных работ на строительном объекте назначить {responsible_employee_post} {responsible_employee_full_name}; [optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п. 1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};[optional-section-end|subresponsible_employee]</li><li>Допустить к самостоятельному выполнению электрогазосварочных работ, прошедших обучение и проверку знаний в установленном порядке:{workers_list}</li><li>Лицам, указанным в п. 1.[optional-section-start|subresponsible_employee] и п. 2.[optional-section-end|subresponsible_employee] настоящего приказа, обеспечить выполнение требований должностных и производственных инструкций, инструкций по охране труда, пожарной безопасности, утвержденных в {company_name} и других действующих нормативных технических документов при выполнении электрогазосварочных работ.</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Эксплуатация баллонов с газами‡Б-ОТ‡О безопасной эксплуатации баллонов со сжатыми и сжиженными газами‡4‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О безопасной эксплуатации баллонов со сжатыми и сжиженными газами при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью и в соответствии с требованиями Приказа Ростехнадзора от 15.12.2020 г. № 536 &laquo;Об утверждении федеральных норм и правил в области промышленной безопасности &laquo;Правила промышленной безопасности при использовании оборудования, работающего под избыточным давлением&raquo; и Приказа Минтруда России от 11.12.2020 г. № 884н &laquo;Об утверждении Правил по охране труда при выполнении электросварочных и газосварочных работ&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным за исправное состояние и безопасную эксплуатацию баллонов со сжатыми и сжиженными газами на строительном объекте {responsible_employee_post} {responsible_employee_full_name};[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п. 1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};[optional-section-end|subresponsible_employee]</li><li>Допустить к самостоятельному обслуживанию баллонов со сжатыми и сжиженными газами на строительном объекте, прошедших обучение и проверку знаний в установленном порядке:{workers_list}</li><li>Персоналу, обслуживающему баллоны со сжатым и сжиженным газами, обеспечить выполнение требований должностных и производственных инструкций, инструкций по охране труда, пожарной безопасности и других действующих нормативных технических документов по эксплуатации и обслуживанию баллонов со сжатыми и сжиженными газами.</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Производство работ с применением подъемных сооружений‡ПС‡Об организации безопасного производства работ с применением подъемных сооружений‡4‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;Об организации безопасного производства работ с применением подъемных сооружений при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В целях обеспечения требований безопасности на строительном объекте и в соответствии с требованиями Приказа Ростехнадзора от 26.11.2020 г. № 461 &laquo;Об утверждении федеральных норм и правил в области промышленной безопасности &laquo;Правила безопасности опасных производственных объектов, на которых используются подъемные сооружения&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным за безопасное производство работ с применением подъемных сооружений на строительном объекте {responsible_employee_post} {responsible_employee_full_name}; [optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п. 1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};[optional-section-end|subresponsible_employee]</li><li>Ответственным за безопасное производство работ с применением подъемных сооружений до начала работ на объекте ознакомить личный состав &laquo;под роспись&raquo;:<br />a) С настоящим приказом<br />б) С технологическими картами;<br />в) ППР;</li><li>Ответственным, за безопасное производство работ с применением подъемных сооружений до начала работ на объекте обеспечить:</li><ol><li>Наличие на месте производства работ схем строповки грузов и складирования грузов;</li><li>ограждение опасной зоны работы крана, установку знаков безопасности;</li><li>проверку у персонала наличия удостоверений на право работы с подъёмными сооружениями;</li><li>выдачу персоналу задания с указанием характера работы и особенностями ее выполнения;</li><li>место установки подъёмного сооружения (крана) в соответствии с ППР;</li><li>проверку наличия вахтенного журнала крановщика, аптечки первой помощи и огнетушителя в кабине крана;</li><li>внесение записи о разрешении выполнения работ краном в вахтенном журнале крановщика;</li></ol><li>Для зацепки, обвязки (строповки) и навешивания груза на крюк крана назначить стропальщиков:{workers_list}</li><li>Ответственным специалистам и обслуживающему персоналу в своей работе руководствоваться ФНП Приказом Федеральной службы по экологическому, технологическому и атомному надзору от 26.11.2020 г. №461 &laquo;Об утверждении федеральных норм и правил в области промышленной безопасности &laquo;Правила безопасности опасных производственных объектов, на которых используются подъёмные сооружения&raquo;, должностными и производственными инструкциями по безопасному производству работ с применением подъёмных сооружений;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Осмотр съемных грузозахватных приспособлений‡СГП‡О назначении ответственного за осмотр съемных грузозахватных приспособлений во время эксплуатации, установки и демонтажа‡1‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за осмотр съемных грузозахватных приспособлений во время эксплуатации, установки и демонтажа при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В целях обеспечения требований безопасности на строительном объекте и в соответствии с требованиями Приказа Ростехнадзора от 26.11.2020 г. № 461 &laquo;Об утверждении федеральных норм и правил в области промышленной безопасности &laquo;Правила безопасности опасных производственных объектов, на которых используются подъемные сооружения&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Ответственным за осмотр съемных грузозахватных приспособлений во время эксплуатации, установки и демонтажа на строительном объекте назначить {responsible_employee_post} {responsible_employee_full_name};[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п. 1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};[optional-section-end|subresponsible_employee]</li><li>Специалисту, ответственному за осмотр съемных грузозахватных приспособлений:</li><ol><li>Не допускать к использованию немаркированные, неисправные, или не соответствующие характеру и массе грузов съемные грузозахватные приспособления;</li><li>Удалять с места производства работ поврежденные и бракованные грузозахватные приспособления;</li><li>Проводить периодический осмотр съемных грузозахватных приспособлений, не реже чем:</li><ol><li>траверс, клещей, захватов &ndash; каждый месяц;</li><li>стропов (за исключением редко используемых) &ndash;каждые 10 дней;</li><li>редко используемых съемных грузозахватных приспособлений &ndash;перед началом работ;</li></ol><li>Результаты осмотра и браковки заносить в журнал осмотра грузозахватных приспособлений.</li></ol><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Производство геодезических работ‡Г‡О назначении ответственного за производство геодезических работ‡5‡<p>&nbsp;</p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за производство геодезических работ при выполнении полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным за производство геодезических работ на строительном объекте {responsible_employee_post} {responsible_employee_full_name} с исполнением следующих обязанностей:</li><ol><li>Принимать и подписывать акты приемки-передачи ГРО;</li><li>Производить вынос осей;</li><li>Использовать поверенное оборудование;</li><li>Осуществлять геодезическую съемку;</li><li>Подписывать исполнительные схемы;</li><li>Выполнять математическую обработку результатов геодезических измерений с помощью компьютерной техники;</li><li>Принимать от заказчика разбивочную основу и выполнять разбивочные работы в процессе строительства;</li><li>Сообщать главному инженеру, заместителю Генерального директора по строительству и представителям заказчика о нарушениях требований проекта;</li><li>Осуществлять контроль за отклонениями в процессе производства строительно-монтажных работ;</li><li>Своевременно проводить исполнительные съемки, в том числе съемки подземных коммуникаций в открытых траншеях, с составлением необходимой исполнительной документации;</li><li>Осуществлять выборочный контроль работ, выполняемых производственным линейным персоналом, в части соблюдения точности геометрических параметров;</li><li>Немедленно уведомлять руководство организации в случае угрозы аварии здания, сооружения, вызванной нарушениями требований проекта в части точности геометрических параметров, с записью в общем журнале работ;</li><li>Осуществлять контроль за состоянием геодезических приборов, средств линейных измерений, правильностью их хранения и эксплуатации;</li><li>Обеспечивать сохранность принятых геодезических знаков на строительной площадке и неизменность их положения в процессе строительства;</li></ol><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Введение режима повышенной готовности‡РПГ‡О введении режима повышенной готовности‡1‡<p>&nbsp;</p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О введении режима повышенной готовности при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В целях обеспечения требований Постановления правительства Санкт-Петербурга от 13 марта 2020 года № 121 &laquo;О мерах по противодействию распространению в Санкт-Петербурге новой коронавирусной инфекции (COVID-19)&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Ввести режим повышенной готовности на строительном объекте.</li><li>Назначить ответственным лицом за организацию и контроль соблюдения рекомендаций по профилактике и распространению новой коронавирусной инфекции среди работников {responsible_employee_post} {responsible_employee_full_name};[optional-section-start|subresponsible_employee]</li><li>На время отсутствия: болезни, отпуска и т. д. {responsible_employee_post} {responsible_employee_name_initials_after} обязанности по исполнению п. 1 данного приказа возложить на {subresponsible_employee_post} {subresponsible_employee_full_name};[optional-section-end|subresponsible_employee]</li><li>Ответственному лицу за организацию и контроль соблюдения рекомендаций по профилактике и распространению новой коронавирусной инфекции среди работников:<ol><li>Выполнять контроль признаков инфекционного заболевания у работников;</li><li>Проводить измерения температуры тела работников перед работой и производить соответствующие записи в &laquo;Журнал измерений температуры тела работников&raquo;;</li><li>Направлять домой работников с повышенной температурой тела для получения медпомощи;</li></ol></li><li>Ввести масочный режим. Смену масок и их обработку производить в соответствии с инструкцией по использованию;</li><li>Всем работникам компании для предупреждения распространения коронавирусной инфекции необходимо:<ol><li>Соблюдать правила личной и общественной гигиены, мыть руки с мылом после улицы, в течение всего рабочего дня и после каждого посещения туалета;</li><li>Использовать антисептики, установленные в бытовках для дезинфекции рук;</li><li>Исключать приветственные рукопожатия, а также телесный контакт в течение рабочего дня;</li><li>Проветривать помещения каждые 2 часа;</li><li>Осуществлять прием пищи, выдерживая дистанцию между друг другом не менее 1,5 метров. Во время приема пищи использовать индивидуальную или одноразовую посуду;</li></ol></li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Приемка законченных работ‡СРО‡О назначении ответственного за приемку законченных видов и отдельных этапов работ‡6‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за приемку законченных видов и отдельных этапов работ по строительству при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В целях обеспечения требований Постановления правительства Санкт-Петербурга от 13 марта 2020 года № 121 &laquo;О мерах по противодействию распространению в Санкт-Петербурге новой коронавирусной инфекции (COVID-19)&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным за приемку законченных видов и отдельных этапов работ по строительству {object_responsible_employee_post} {object_responsible_employee_full_name}, идентификационный номер Специалиста {object_responsible_employee_sro_number};</li><li>Лицу, ответственному за приемку законченных видов и отдельных этапов работ по строительству подписывать следующие документы:<ol><li>Акты освидетельствования скрытых работ;</li><li>Документы, подтверждающие соответствие выполненных работ требованиям технических регламентов;</li></ol></li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Контроль по охране труда‡ОТК‡О назначении специалиста по охране труда‡7‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении специалиста по охране труда при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В целях обеспечения требований Постановления правительства Санкт-Петербурга от 13 марта 2020 года № 121 &laquo;О мерах по противодействию распространению в Санкт-Петербурге новой коронавирусной инфекции (COVID-19)&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>1. Назначить ответственным лицом, по выборочному контролю, за выполнением мероприятий по охране труда на объекте строительства {main_labor_safety_employee_post} {main_labor_safety_employee_full_name} (тел. {main_labor_safety_employee_phone});</li><li>Специалисту по охране труда руководствоваться своими должностными инструкциями, действующими локальными нормативно-правовыми актами, регламентами, указаниями контролирующих органов;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Допуск персонала‡ДП‡О допуске персонала, обслуживающего подъемные сооружения на строительном объекте‡2‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О допуске персонала, обслуживающего подъемные сооружения при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью и в соответствии с требованиями Приказа Ростехнадзора от 26.11.2020 г. № 461 &laquo;Об утверждении федеральных норм и правил в области промышленной безопасности &laquo;Правила безопасности опасных производственных объектов, на которых используются подъемные сооружения&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Допустить к работе в качестве персонала, обслуживающего подъемные сооружения при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: {workers_list}</li><li>Персоналу, обслуживающему подъемные сооружения, обеспечить выполнение требований должностных и производственных инструкций, инструкций по охране труда, пожарной безопасности и других действующих нормативных технических документов по эксплуатации подъемных сооружений с навесным оборудованием;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Допуск электрогазосварщиков‡ДСВ‡О допуске электрогазосварщиков к работе‡2‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О допуске электрогазосварщиков к работе при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В соответствии с требованиями Постановления Правительства РФ от 16.09.2020 г. № 1479 &laquo;Об утверждении Правил противопожарного режима в Российской Федерации&raquo;, Приказа Минтруда России от 11.12.2020 г. № 884н &laquo;Об утверждении Правил по охране труда при выполнении электросварочных и газосварочных работ&raquo;, ГОСТа 12.3.003-86 &laquo;ССБТ. Работы электросварочные. Требования безопасности&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Допустить к самостоятельному выполнению электрогазосварочных работ при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте, следующих сотрудников, прошедших обучение и проверку знаний в установленном порядке: {workers_list}</li><li>Персоналу при выполнении электрогазосварочных работ обеспечить выполнение требований производственных инструкций, инструкций по охране труда, пожарной безопасности, утвержденных в Компании и других действующих нормативных технических документов при выполнении электрогазосварочных работ;</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Подготовка, оформление и подписание исполнительной документации‡ИС‡О назначении ответственных лиц за подготовку, оформление и подписание исполнительной документации‡4‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственных лиц за подготовку, оформление и подписание исполнительной документации при производстве строительно-монтажных работ по погружению и извлечению шпунтового ограждения, монтажу и демонтажу системы крепления на объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с производственной необходимостью,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным лицом за подготовку и оформление исполнительной документации {responsible_engineer_post} {responsible_engineer_name};</li><li>Назначить ответственными за подготовку и подписание исполнительной документации:{workers_list}</li><li>Ответственность за заверение копий документов подписью и печатью, подписание документов, подтверждающих выполнение работ, оставляю за собой</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Разработка проектной документации‡П‡О назначении ответственного специалиста за разработку проектной документации‡2‡<p><br/></p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного специалиста за разработку проектной документации при производстве комплекса работ по устройству шпунтового ограждения на объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В связи с необходимостью разработки проектной документации для строительного объекта,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Утвердить список специалистов ответственных за разработку проектной документации для объекта в следующем составе:{workers_list}</li><li>Ответственность за заверение копий документов подписью и печатью, подписание документов, подтверждающих выполнение работ, оставляю за собой</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Охрана окружающей среды‡ЭК‡О назначении ответственных лиц за охрану окружающей среды, обеспечение экологической безопасности, обращение с отходами‡8‡<p>&nbsp;</p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственных лиц за охрану окружающей среды, обеспечение экологической безопасности, обращение с отходами при производстве комплекса работ по устройству шпунтового ограждения для строительства на объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>Во исполнение Федерального закона &laquo;Об охране окружающей среды&raquo; № 7-ФЗ от 10.01.2002 г., Федерального закона &laquo;Об отходах производства и потребления&raquo; № 89-ФЗ от 24.06.1998 г. и иных действующих нормативных актов РФ, направленных на охрану окружающей среды, обеспечение экологической безопасности и обращение с отходами,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить {responsible_employee_post} {responsible_employee_full_name} лицом, ответственным за соблюдение требований в области охраны окружающей среды, экологической безопасности и обращения с отходами при выполнении работ по Договору строительного подряда на указанном объекте строительства;</li><li>Назначить лицом, ответственным за проведение производственного экологического контроля {responsible_labor_safety_employee_post} {responsible_labor_safety_employee_full_name};</li><li>Организацию производственного и экологического контроля и исполнение настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Направление работников и назначение ответственных в выходные дни‡Н-ВЫХ‡О направлении работников на строительный объект и назначении ответственного за производство работ в выходные дни‡9‡<br/>',
                'Приемка электрогазосварочных работ‡СК‡Об организации приемки и контроля качества электрогазосварочных сварочных работ‡10‡<p>&nbsp;</p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;Об организации приемки и контроля качества электрогазосварочных сварочных работ при производстве полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;</p></td><td>&nbsp;</td></tr></tbody></table><p><br /><br /></p><p>В соответствии с Приказом от 11 декабря 2020 года № 883н Министерства труда и социальной защиты РФ &laquo;Об утверждении Правил по охране труда при строительстве, реконструкции и ремонте&raquo; и Приказом Министерства труда и социальной защиты РФ от 11.12.2020г. № 884н &laquo;Об утверждении Правил по охране туда при выполнении электросварочных и газосварочных работ&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным лицом за приемку и контроль качества электрогазосварочных работ {gas_welding_works_employee_post} {gas_welding_works_employee_full_name}, удостоверение № {gas_welding_works_employee_certificate};</li><li>Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><p><br /><br /></p>{sign_list}',
                'Доверенность‡Доверенность‡Доверенность‡11‡<br/>',
                'Сопроводительное письмо‡Сопроводительное письмо‡Сопроводительное письмо‡12‡<p><br></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>Исх. № б/н от {order_date} г.</p></td><td><p style="text-align: right;"><strong>{contractor_name}</strong></p></td></tr></tbody></table><p style="text-align: center; font-size: 20px;"><strong>Сопроводительное письмо</strong></p><p>Настоящим направляем Вам документы по охране труда, необходимые к предоставлению перед началом полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}, на земельном участке с кадастровым номером {project_object_cadastral_number}&raquo;, а именно:</p>{generated_orders_list}<p><br /><br /><br /></p><table style="width: 100%; height: 36px;"><tbody><tr style="height: 76px;"><td style="width: 20%; height: 10px;"><p>Документы получены:</p></td><td style="width: 80%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(Должность, Ф.И.О., подпись, дата получения)</span></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;"><strong>Генеральный директор {company_name}</strong></p></td><td><p style="text-align: right;"><strong>М. Д. Исмагилов</strong></p></td></tr></tbody></table><p>&nbsp;</p>'];

            foreach ($laborSafetyOrderTypesArray as $laborSafetyOrderTypeElement) {
                $laborSafetyOrderType = new LaborSafetyOrderType([
                    'name' => explode('‡', $laborSafetyOrderTypeElement)[0],
                    'short_name' => explode('‡', $laborSafetyOrderTypeElement)[1],
                    'full_name' => explode('‡', $laborSafetyOrderTypeElement)[2],
                    'order_type_category_id' => explode('‡', $laborSafetyOrderTypeElement)[3],
                    'template' => explode('‡', $laborSafetyOrderTypeElement)[4],
                ]);
                $laborSafetyOrderType->save();
            }
        }

        if (! Schema::hasTable('labor_safety_request_statuses')) {
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
                'Завершена',
            ];

            foreach ($laborSafetyRequestStatusArray as $laborSafetyRequestStatusElement) {
                $laborSafetyRequestStatus = new LaborSafetyRequestStatus([
                    'name' => $laborSafetyRequestStatusElement,
                ]);
                $laborSafetyRequestStatus->save();
            }

        }
        if (! Schema::hasTable('labor_safety_requests')) {
            Schema::create('labor_safety_requests', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('order_number')->default('б/н')->comment('Номер приказа');
                $table->date('order_date')->index()->comment('Дата приказа');
                $table->integer('company_id')->unsigned()->comment('ID компании');
                $table->integer('project_object_id')->unsigned()->comment('ID объекта');
                $table->integer('author_user_id')->unsigned()->comment('ID автора');
                $table->integer('implementer_user_id')->unsigned()->nullable()->comment('ID исполнителя');
                $table->bigInteger('responsible_employee_id')->unsigned()->comment('ID ответственного сотрудника');
                $table->bigInteger('sub_responsible_employee_id')->unsigned()->nullable()->comment('ID замещающего ответственного сотрудника');
                $table->integer('request_status_id')->unsigned()->comment('ID статуса заявки');
                $table->mediumText('generated_html')->nullable()->comment('Сформированные приказы');
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

        if (! Schema::hasTable('labor_safety_request_orders')) {
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

        if (! Schema::hasTable('labor_safety_worker_types')) {
            Schema::create('labor_safety_worker_types', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->string('name')->comment('Значение');

                $table->timestamps();
                $table->softDeletes();
            });
        }
        DB::statement("ALTER TABLE labor_safety_worker_types COMMENT 'Типы сотрудников (рабочих), для формирования приказов в модуле «Охрана труда»'");

        $laborSafetyWorkerTypesArray = ['Ответственный',
            'Заместитель ответственного',
            'Сотрудник',
            'Ответственный за приемку работ [СРО]',
            'Ответственный по охране труда [ОТК]',
            'Ответственный за исполнительную документацию [ИС]',
            'Ответственный за проведение экологического контроля [ЭК]',
            'Ответственный за электрогазосварочные работы [СК]',
        ];

        foreach ($laborSafetyWorkerTypesArray as $laborSafetyWorkerTypesElement) {
            $laborSafetyWorkerTypes = new LaborSafetyWorkerType([
                'name' => $laborSafetyWorkerTypesElement,
            ]);
            $laborSafetyWorkerTypes->save();
        }

        if (! Schema::hasTable('labor_safety_request_workers')) {
            Schema::create('labor_safety_request_workers', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('request_id')->unsigned()->comment('ID Заявки');
                $table->bigInteger('worker_employee_id')->unsigned()->comment('ID сотрудника');
                $table->integer('worker_type_id')->unsigned()->comment('ID типа сотрудника');

                $table->foreign('request_id')->references('id')->on('labor_safety_requests');
                $table->foreign('worker_employee_id', 'l_s_r_worker_employee_id_foreign')->references('id')->on('employees');
                $table->foreign('worker_type_id')->references('id')->on('labor_safety_worker_types');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_request_workers COMMENT 'Список сотрудников (рабочих), для которых необходимо сформировать приказы в модуле «Охрана труда»'");
        }

        if (! Schema::hasTable('labor_safety_order_workers')) {
            Schema::create('labor_safety_order_workers', function (Blueprint $table) {
                $table->increments('id')->comment('Уникальный идентификатор');
                $table->integer('request_id')->unsigned()->comment('ID заявки');
                $table->integer('order_type_id')->unsigned()->comment('ID типа приказа');
                $table->integer('requests_worker_id')->unsigned()->comment('ID записи со ссылкой на сотрудника, сформированному при подаче заявки');

                $table->foreign('request_id')->references('id')->on('labor_safety_requests');
                $table->foreign('order_type_id')->references('id')->on('labor_safety_order_types');
                $table->foreign('requests_worker_id')->references('id')->on('labor_safety_request_workers');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE labor_safety_order_workers COMMENT 'Список сотрудников (рабочих), которые участвуют при формировании приказов в модуле «Охрана труда»'");
        }

        (new employeeSubdivisionsSeeder)->run();
        (new employeePostsSeeder)->run();
        (new employeesSeeder)->run();
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

        Schema::dropIfExists('labor_safety_order_workers');
        Schema::dropIfExists('labor_safety_request_workers');
        Schema::dropIfExists('labor_safety_worker_types');
        Schema::dropIfExists('labor_safety_request_orders');
        Schema::dropIfExists('labor_safety_requests');

        Schema::dropIfExists('employees_1c_post_inflections');
        Schema::dropIfExists('employee_name_inflections');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('employees_1c_posts');
        Schema::dropIfExists('employees_1c_subdivisions');
        Schema::dropIfExists('employees_report_groups');

        Schema::dropIfExists('company_report_templates');
        Schema::dropIfExists('company_report_template_types');
        Schema::dropIfExists('companies');

        Schema::dropIfExists('labor_safety_request_statuses');
        Schema::dropIfExists('labor_safety_order_types');
        Schema::dropIfExists('labor_safety_order_type_categories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('inn');
            $table->dropColumn('gender');
        });
    }
};

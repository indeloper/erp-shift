<?php

namespace Database\Seeders;

use App\Models\Company\Company;
use App\Models\Employees\Employees1cSubdivision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class employeeSubdivisionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subdivisions = json_decode('{"data": [
{
"subdivisionUID": "f5467e1c-ff45-11e7-80c5-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Склад",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "dba907d3-80db-11e8-80fb-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Проектный отдел",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "70804c36-d74b-11ec-810a-1831bfcfda3c",
"subdivisionParentUID": "",
"subdivisionName": "Отдел охраны труда и промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "7a0656de-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "5889fc1d-7606-11e9-8123-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Отдел материально-технического снабжения и логистики (ОМТС и логистики)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "de94c1b1-3d13-11e2-afa6-0019d11ffeaf",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "842c12dd-3a0f-11e2-a4d2-0019d11ffeaf",
"organizationINN": ""
},
{
"subdivisionUID": "abfc4eb7-9ac7-11e8-80fd-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "2",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Шпунтовое направление",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "7a0656dc-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Коммерческий отдел",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "ded5510f-fdc3-11ec-810a-1831bfcfda3c",
"subdivisionParentUID": "",
"subdivisionName": "Тендерный отдел",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "51f328de-3a11-11e2-a4d2-0019d11ffeaf",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "abfc4ec1-9ac7-11e8-80fd-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Склад",
"organizationUID": "842c12dd-3a0f-11e2-a4d2-0019d11ffeaf",
"organizationINN": ""
},
{
"subdivisionUID": "f75d8be9-a6a8-11ec-810a-1831bfcfda3c",
"subdivisionParentUID": "",
"subdivisionName": "Планово-экономический отдел",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "7a0656dd-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Бухгалтерия",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "2803b067-65a3-11e5-84a7-50465d8f7441",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "51f328df-3a11-11e2-a4d2-0019d11ffeaf",
"subdivisionParentUID": "",
"subdivisionName": "Строительное подразделение",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "abfc4ebb-9ac7-11e8-80fd-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "1",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "0e56c364-439c-11e2-b095-0019d11ffeaf",
"subdivisionParentUID": "",
"subdivisionName": "Строительный участок- ШПУНТ",
"organizationUID": "842c12dd-3a0f-11e2-a4d2-0019d11ffeaf",
"organizationINN": ""
},
{
"subdivisionUID": "7cfcb23d-2ce3-11ec-8107-1831bfcfda3c",
"subdivisionParentUID": "",
"subdivisionName": "Договорный отдел",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "7a0656d6-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Техническое подразделение",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "0782e8b3-77ab-11e9-8123-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Административно-хозяйственный отдел (АХО)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "2eab7a63-7bfe-11e6-b771-50465d8f7441",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "2eab7a61-7bfe-11e6-b771-50465d8f7441",
"organizationINN": "7810950525"
},
{
"subdivisionUID": "7a0656db-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Производственно-технический отдел (ПТО)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "fe11f0dd-abbf-11e6-9641-50465d8f7441",
"subdivisionParentUID": "",
"subdivisionName": "Управление механизацией и техникой (УМиТ)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "2acd165f-8a67-11ec-8107-1831bfcfda3c",
"subdivisionParentUID": "",
"subdivisionName": "Отдел информационных технологий",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "abfc4ebe-9ac7-11e8-80fd-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Строительный участок- СВАИ",
"organizationUID": "842c12dd-3a0f-11e2-a4d2-0019d11ffeaf",
"organizationINN": ""
},
{
"subdivisionUID": "7a0656da-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "",
"subdivisionName": "Отдел персонала",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "1d9a31bf-23f9-11e7-a22c-50465d8f7441",
"subdivisionParentUID": "51f328df-3a11-11e2-a4d2-0019d11ffeaf",
"subdivisionName": "Свайное направление",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "2ede92e3-6302-11e8-80fb-00155d4c1e00",
"subdivisionParentUID": "51f328df-3a11-11e2-a4d2-0019d11ffeaf",
"subdivisionName": "Отдел строительного контроля (ОСК)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "6b25fae2-65d9-11e5-84a7-50465d8f7441",
"subdivisionParentUID": "2803b067-65a3-11e5-84a7-50465d8f7441",
"subdivisionName": "Строительный участок",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "3b0223fe-d751-11e8-8110-00155d630402",
"subdivisionParentUID": "51f328df-3a11-11e2-a4d2-0019d11ffeaf",
"subdivisionName": "Общестроительное направление",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "7a0656d8-dfe6-11e7-80c3-00155d4c1e00",
"subdivisionParentUID": "51f328de-3a11-11e2-a4d2-0019d11ffeaf",
"subdivisionName": "Финансовый отдел",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494"
},
{
"subdivisionUID": "67808a97-5f34-11ec-8107-1831bfcfda3c",
"subdivisionParentUID": "",
"subdivisionName": "Сметно-экономический отдел",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228"
},
{
"subdivisionUID": "e0b05268-9d8e-11e9-812f-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Строительное подразделение",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228"
},
{
"subdivisionUID": "e0b05267-9d8e-11e9-812f-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228"
},
{
"subdivisionUID": "e0b05269-9d8e-11e9-812f-00155d630402",
"subdivisionParentUID": "e0b05268-9d8e-11e9-812f-00155d630402",
"subdivisionName": "Свайное направление",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228"
},
{
"subdivisionUID": "70cb9f8e-fd05-11ec-810a-1831bfcfda3c",
"subdivisionParentUID": "e0b05267-9d8e-11e9-812f-00155d630402",
"subdivisionName": "Тендерный отдел",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228"
},
{
"subdivisionUID": "a6244a24-a2e6-11e9-812f-00155d630402",
"subdivisionParentUID": "e0b05267-9d8e-11e9-812f-00155d630402",
"subdivisionName": "Бухгалтерия",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228"
},
{
"subdivisionUID": "a8983b2e-aa37-11ea-8141-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Производственный участок ШПУНТ",
"organizationUID": "3e7ef6e7-9c13-11e9-812f-00155d630402",
"organizationINN": "7807227475"
},
{
"subdivisionUID": "a8983b2f-aa37-11ea-8141-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Производственный участок СВАИ",
"organizationUID": "3e7ef6e7-9c13-11e9-812f-00155d630402",
"organizationINN": "7807227475"
},
{
"subdivisionUID": "baac25ce-1773-11eb-80ff-1831bfcfda3c",
"subdivisionParentUID": "",
"subdivisionName": "Склад",
"organizationUID": "3e7ef6e7-9c13-11e9-812f-00155d630402",
"organizationINN": "7807227475"
},
{
"subdivisionUID": "76680892-9cd3-11e9-812f-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "3e7ef6e7-9c13-11e9-812f-00155d630402",
"organizationINN": "7807227475"
},
{
"subdivisionUID": "76680893-9cd3-11e9-812f-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Производственный участок",
"organizationUID": "3e7ef6e7-9c13-11e9-812f-00155d630402",
"organizationINN": "7807227475"
},
{
"subdivisionUID": "db46bb1f-23a8-11e9-8111-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "7d1f39f2-59c7-11e9-811b-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Производственный участок СВАИ",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "7e1de6a4-60e4-11e9-811d-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "СКЛАД",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "239cc409-a153-11e9-812f-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Лаборатория неразрущающего контроля",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "db46bb20-23a8-11e9-8111-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Производственный участок",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "96c4b484-2848-11e9-8111-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Производственный участок",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525"
},
{
"subdivisionUID": "96c4b485-2848-11e9-8111-00155d630402",
"subdivisionParentUID": "",
"subdivisionName": "Администрация",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525"
},
{
"subdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"subdivisionParentUID": "db46bb20-23a8-11e9-8111-00155d630402",
"subdivisionName": "Производственный участок ШПУНТ",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "b5600079-59fb-11e9-811b-00155d630402",
"subdivisionParentUID": "db46bb20-23a8-11e9-8111-00155d630402",
"subdivisionName": "Производственный участок СВАИ",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "b560007b-59fb-11e9-811b-00155d630402",
"subdivisionParentUID": "db46bb20-23a8-11e9-8111-00155d630402",
"subdivisionName": "СКЛАД",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
},
{
"subdivisionUID": "109705c9-836d-11e9-8123-00155d630402",
"subdivisionParentUID": "db46bb20-23a8-11e9-8111-00155d630402",
"subdivisionName": "Лаборатория неразрушающего контроля",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806"
}
]
                  }', false);

        foreach ($subdivisions->data as $subdivision) {
            $company = Company::where('company_1c_uid', $subdivision->organizationUID)->get()->first();

            Log::channel('stderr')->info($subdivision->subdivisionParentUID);

            $subdivisionParentId = null;

            if (! empty($subdivision->subdivisionParentUID)) {
                $subdivisionParentId = Employees1cSubdivision::where('subdivision_1c_uid', '=', $subdivision->subdivisionParentUID)->get()->first();
                if (isset($subdivisionParentId)) {
                    $subdivisionParentId = $subdivisionParentId->id;
                }
            }

            if (isset($company)) {
                Employees1cSubdivision::updateOrCreate(
                    [
                        'subdivision_1c_uid' => $subdivision->subdivisionUID,
                    ],
                    [
                        'subdivision_parent_id' => $subdivisionParentId,
                        'name' => $subdivision->subdivisionName,
                        'company_id' => $company->id,
                    ]
                );
            }
        }
    }
}

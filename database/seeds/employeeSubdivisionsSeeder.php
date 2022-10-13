<?php

use App\Models\Company\Company;
use App\Models\OneC\Employees1cSubdivision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;

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
                        "subdivisionUID": "67808a97-5f34-11ec-8107-1831bfcfda3c",
                      "subdivisionParentUID": "",
                      "subdivisionName": "Сметно-экономический отдел",
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
                    }
                  ]}', false);

        foreach ($subdivisions->data as $subdivision) {
            $company = Company::where('company_1c_uid', $subdivision->organizationUID)->get()->first();

            Log::channel('stderr')->info($subdivision->subdivisionParentUID);

            $subdivisionParentId = null;

            if (!empty($subdivision->subdivisionParentUID)) {
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
                        "subdivision_parent_id" => $subdivisionParentId,
                        "name" => $subdivision->subdivisionName,
                        "company_id" => $company->id
                    ]
                );
            }
        }
    }
}

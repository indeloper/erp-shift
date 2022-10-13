<?php

use App\Models\Company\Company;
use App\Models\OneC\Employee;
use App\Models\OneC\Employees1cpost;
use App\Models\OneC\Employees1cSubdivision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;

class employeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $employees = json_decode('{"data": [
                                            {
                                            "employeeUID": "ea79384b-0769-11ed-810a-1831bfcfda3c",
                                            "personnelNumber": "0000-00059",
                                            "employeeName": "Харченко Александр Викторович",
                                            "employeeLastName": "Харченко",
                                            "employeeFirstName": "Александр",
                                            "employeePatronymic": "Викторович",
                                            "employeePhone": "8-921-416-87-43",
                                            "employee1CPostUID": "e0b0526a-9d8e-11e9-812f-00155d630402",
                                            "employee1CSubdivisionUID": "e0b05269-9d8e-11e9-812f-00155d630402",
                                            "individual1CCode": "00-0000059",
                                            "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                            "organizationINN": "7807115228",
                                            "birthday": "1964-10-17",
                                            "dateReceived": "2022-07-20",
                                            "dismissalDate": ""
                                            },
                                            {
                                            "employeeUID": "61b35adf-bf66-11e9-8130-00155d630402",
                                            "personnelNumber": "0000-00009",
                                            "employeeName": "Исмагилов Михаил Данилович",
                                            "employeeLastName": "Исмагилов",
                                            "employeeFirstName": "Михаил",
                                            "employeePatronymic": "Данилович",
                                            "employeePhone": "8-911-927-33-88",
                                            "employee1CPostUID": "85db1cf5-9d6d-11e9-812f-00155d630402",
                                            "employee1CSubdivisionUID": "e0b05267-9d8e-11e9-812f-00155d630402",
                                            "individual1CCode": "00-0000001",
                                            "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                            "organizationINN": "7807115228",
                                            "birthday": "1980-04-09",
                                            "dateReceived": "2019-08-16",
                                            "dismissalDate": ""
                                            },
                                            {
                                            "employeeUID": "0664bcee-fa3d-11e9-8136-00155d630402",
                                            "personnelNumber": "0000-00020",
                                            "employeeName": "Краев Илья Сергеевич",
                                            "employeeLastName": "Краев",
                                            "employeeFirstName": "Илья",
                                            "employeePatronymic": "Сергеевич",
                                            "employeePhone": "8 (900) 620-23-85",
                                            "employee1CPostUID": "e0b0526a-9d8e-11e9-812f-00155d630402",
                                            "employee1CSubdivisionUID": "e0b05269-9d8e-11e9-812f-00155d630402",
                                            "individual1CCode": "00-0000019",
                                            "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                            "organizationINN": "7807115228",
                                            "birthday": "1990-07-11",
                                            "dateReceived": "2019-11-01",
                                            "dismissalDate": ""
                                            },
                                            {
                                            "employeeUID": "aad1683f-09bf-11ea-8136-00155d630402",
                                            "personnelNumber": "0000-00022",
                                            "employeeName": "Ероменок Александр Николаевич",
                                            "employeeLastName": "Ероменок",
                                            "employeeFirstName": "Александр",
                                            "employeePatronymic": "Николаевич",
                                            "employeePhone": "8-911-819-29-98",
                                            "employee1CPostUID": "e0b0526b-9d8e-11e9-812f-00155d630402",
                                            "employee1CSubdivisionUID": "e0b05269-9d8e-11e9-812f-00155d630402",
                                            "individual1CCode": "00-0000021",
                                            "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                            "organizationINN": "7807115228",
                                            "birthday": "1975-10-28",
                                            "dateReceived": "2019-11-18",
                                            "dismissalDate": ""
                                            }
                                            ]}', false);

        foreach ($employees->data as $employee) {
            $company = Company::where('company_1c_uid', $employee->organizationUID)->get()->first();
            $employeePost = Employees1cPost::where('post_1c_uid', '=', $employee->employee1CPostUID)->get()->first();
            $employeeSubdivision = Employees1cSubdivision::where('subdivision_1c_uid', '=', $employee->employee1CSubdivisionUID)->get()->first();


            $formattedBirthday = Carbon::parse($employee->birthday)->format('d.m.Y');
            $formattedPhone = preg_replace("/[^0-9]/", '', $employee->employeePhone);
            if (substr($formattedPhone, 0, 1) == 8) {
                $formattedPhone = substr_replace($formattedPhone, '7', 0, 1);
            }

            Log::channel('stderr')->info($employee->employeeFirstName.'+'.$employee->employeeLastName.'+'.$employee->employeePatronymic);

            if (isset($company)) {
                $user = User::withoutGlobalScopes()->updateOrCreate(
                    [
                        'first_name' => trim($employee->employeeFirstName),
                        'last_name' => trim($employee->employeeLastName),
                        'patronymic' => trim($employee->employeePatronymic),
                        'birthday' => trim($formattedBirthday)
                    ],
                    [
                        'person_phone' => $formattedPhone
                    ]
                );

                Employee::updateOrCreate(
                    [
                        'employee_1c_uid' => $employee->employeeUID,
                    ],
                    [
                        'user_id' => $user->id,
                        'employee_1c_name' => $employee->employeeName,
                        'personnel_number' => $employee->personnelNumber,
                        'employee_1c_post_id' => $employeePost->id,
                        'employee_1c_subdivision_id' => $employeeSubdivision->id,
                        'company_id' => $company->id,
                        'employment_date' => $employee->dateReceived,
                        'dismissal_date' => $employee->dismissalDate,
                        'report_group_id' => null
                    ]
                );
            }
        }
    }
}

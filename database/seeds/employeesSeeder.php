<?php

use App\Models\Company\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\Employees\Employees1cSubdivision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;
use Telegram\Bot\Laravel\Facades\Telegram;

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
"employeeUID": "08d31e81-102a-11ee-80bd-000c29565159",
"personnelNumber": "0000-00087",
"employeeINN": "470313724230",
"employeeGender": "M",
"employeeName": "Чулков Николай Николаевич",
"employeeLastName": "Чулков",
"employeeFirstName": "Николай",
"employeePatronymic": "Николаевич",
"employeePhone": "8905-282-46-38",
"employee1CPostUID": "d3dc5e62-9e35-11e9-812f-00155d630402",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "e0b05269-9d8e-11e9-812f-00155d630402",
"individual1CCode": "00-0000087",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"birthday": "1985-04-20",
"dateReceived": "2023-06-22",
"dismissalDate": "",
"inflection": {
"nominative": "Чулков Николай Николаевич",
"genitive": "Чулкова Николая Николаевича",
"dative": "Чулкову Николаю Николаевичу",
"accusative": "Чулкова Николая Николаевича",
"ablative": "Чулковым Николаем Николаевичем",
"prepositional": "Чулкове Николае Николаевиче"
}
}]}', false);

        foreach ($employees->data as $employee) {
            $company = Company::where('company_1c_uid', $employee->organizationUID)->get()->first();
            $employeePost = Employees1cPost::where('post_1c_uid', '=', $employee->employee1CPostUID)->get()->first();
            $employeeSubdivision = Employees1cSubdivision::where('subdivision_1c_uid', '=', $employee->employee1CSubdivisionUID)->get()->first();

            $userStatus = 1;
            if (!empty($employee->dismissalDate) && Carbon::parse($employee->dismissalDate) < Carbon::now()->addDay())
            {
                $userStatus = 0;
            }

            $formattedBirthday = Carbon::parse($employee->birthday)->format('d.m.Y');
            $formattedPhone = preg_replace("/[^0-9]/", '', $employee->employeePhone);
            if (substr($formattedPhone, 0, 1) == 8) {
                $formattedPhone = substr_replace($formattedPhone, '7', 0, 1);
            }

            if (isset($company)) {
                /*$user = User::withoutGlobalScopes()
                    ->where('first_name', '=', trim($employee->employeeFirstName))
                    ->where('last_name', '=', trim($employee->employeeLastName))
                    ->where('patronymic', '=', trim($employee->employeePatronymic))
                    ->where('birthday', '=', trim($formattedBirthday))
                    ->get()
                    ->first();*/

                // Need to use that after first sync
                $user = User::withoutGlobalScopes()
                    ->where('inn', '=', trim($employee->employeeINN))
                    ->get()
                    ->first();

                    if (isset($user))
                    {
                        //if ($user->status != 0) { Think about that condition when employee has been already dismissed
                            DB::statement("update users set " .
                                "first_name = '" . trim($employee->employeeFirstName) . "', " .
                                "last_name = '" . trim($employee->employeeLastName) . "', " .
                                "patronymic = '" . trim($employee->employeePatronymic) . "', " .
                                "person_phone = '" . $formattedPhone . "', " .
                                "inn = '" . trim($employee->employeeINN) . "', " .
                                "gender = '" . trim($employee->employeeGender) . "', " .
                                "person_phone = '" . $formattedPhone . "', " .
                                "company = " . $company->id . ", " .
                                "updated_at = NOW(), " .
                                "status = " . $userStatus . " " .
                                "where id = '" . $user->id . "'");
                            Log::channel('stderr')->info('[info] Обновлен пользователь: ' . $employee->employeeLastName . ' ' . $employee->employeeFirstName . ' ' . trim($employee->employeePatronymic));
                        //}
                    } else {
                        DB::statement('insert into users (first_name,' .
                                                                'last_name,' .
                                                                'patronymic,' .
                                                                'inn,' .
                                                                'gender,' .
                                                                'birthday,' .
                                                                'person_phone,' .
                                                                'company,' .
                                                                'created_at,' .
                                                                'updated_at,' .
                                                                'status,' .
                                                                'is_su) ' .
                        'values (' .
                            "'" . trim($employee->employeeFirstName) . "'," .
                            "'" . trim($employee->employeeLastName) . "'," .
                            "'" . trim($employee->employeePatronymic) . "'," .
                            "'" . trim($employee->employeeINN) . "'," .
                            "'" . trim($employee->employeeGender) . "'," .
                            "'" . $formattedBirthday . "'," .
                            "'" . $formattedPhone . "'," .
                                  $company->id . "," .
                                  'NOW()' . "," .
                                  'NOW()' . "," .
                                  '0' . "," .
                                  '0' .
                        ')');

                        Log::channel('stderr')->info('[info] Добавлен новый пользователь: ' . $employee->employeeLastName . ' ' . $employee->employeeFirstName . ' ' . trim($employee->employeePatronymic));

                        /*Telegram::sendMessage([
                            'chat_id' =>  config('app.env') == 'production' ? '-1001505547789' : '-1001558926749',
                            'text' => "[info] Добавлен новый пользователь: " . $employee->employeeLastName . ' ' . $employee->employeeFirstName . ' ' . trim($employee->employeePatronymic)
                        ]);*/
                    }

                $user = User::withoutGlobalScopes()
                    ->where('inn', '=', trim($employee->employeeINN))
                    ->get()
                    ->first();

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

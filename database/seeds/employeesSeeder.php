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
"employeeUID": "ca4ab3ee-9b1c-11ed-80bb-000c29565159",
"personnelNumber": "ГОЗК-00346",
"employeeINN": "780451120950",
"employeeGender": "M",
"employeeName": "Шарипов Мустафо Астанакулович",
"employeeLastName": "Шарипов",
"employeeFirstName": "Мустафо",
"employeePatronymic": "Астанакулович",
"employeePhone": "89516839415",
"employee1CPostUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"individual1CCode": "ЗК-0000071",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"birthday": "1981-11-24",
"dateReceived": "2023-01-24",
"dismissalDate": "",
"inflection": {
"nominative": "Шарипов Мустафо Астанакулович",
"genitive": "Шарипова Мустафо Астанакуловича",
"dative": "Шарипову Мустафо Астанакуловичу",
"accusative": "Шарипова Мустафо Астанакуловича",
"ablative": "Шариповым Мустафо Астанакуловичем",
"prepositional": "Шарипове Мустафо Астанакуловиче"
}
},
{
"employeeUID": "71a3bcf5-9b0e-11ed-80bb-000c29565159",
"personnelNumber": "ГОЗК-00345",
"employeeINN": "226912113716",
"employeeGender": "M",
"employeeName": "Хайдаров Абдусалом Абдурахмонович",
"employeeLastName": "Хайдаров",
"employeeFirstName": "Абдусалом",
"employeePatronymic": "Абдурахмонович",
"employeePhone": "89291147828",
"employee1CPostUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"individual1CCode": "ЗК-0000312",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"birthday": "1975-08-16",
"dateReceived": "2023-01-24",
"dismissalDate": "",
"inflection": {
"nominative": "Хайдаров Абдусалом Абдурахмонович",
"genitive": "Хайдарова Абдусалома Абдурахмоновича",
"dative": "Хайдарову Абдусалому Абдурахмоновичу",
"accusative": "Хайдарова Абдусалома Абдурахмоновича",
"ablative": "Хайдаровым Абдусаломом Абдурахмоновичем",
"prepositional": "Хайдарове Абдусаломе Абдурахмоновиче"
}
},
{
"employeeUID": "e78e13b7-970b-11ed-80bb-000c29565159",
"personnelNumber": "ГОЗК-00344",
"employeeINN": "784105570340",
"employeeGender": "M",
"employeeName": "Паламар Андрей",
"employeeLastName": "Паламар",
"employeeFirstName": "Андрей",
"employeePatronymic": "",
"employeePhone": "89643970774",
"employee1CPostUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"individual1CCode": "ЗК-0000311",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"birthday": "1984-08-19",
"dateReceived": "2023-01-19",
"dismissalDate": "",
"inflection": {
"nominative": "Паламар Андрей",
"genitive": "Паламара Андрея",
"dative": "Паламару Андрею",
"accusative": "Паламара Андрея",
"ablative": "Паламаром Андреем",
"prepositional": "Паламаре Андрее"
}
},
{
"employeeUID": "6563ef7c-90dd-11ed-80bb-000c29565159",
"personnelNumber": "ГОЗК-00343",
"employeeINN": "732813966699",
"employeeGender": "M",
"employeeName": "Малышев Дмитрий Александрович",
"employeeLastName": "Малышев",
"employeeFirstName": "Дмитрий",
"employeePatronymic": "Александрович",
"employeePhone": "+7 (911) 779-09-19",
"employee1CPostUID": "2529f0ae-3a20-11e2-a4d2-0019d11ffeaf",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"individual1CCode": "ЗК-0000305",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"birthday": "1983-09-15",
"dateReceived": "2023-01-11",
"dismissalDate": "",
"inflection": {
"nominative": "Малышев Дмитрий Александрович",
"genitive": "Малышева Дмитрия Александровича",
"dative": "Малышеву Дмитрию Александровичу",
"accusative": "Малышева Дмитрия Александровича",
"ablative": "Малышевым Дмитрием Александровичем",
"prepositional": "Малышеве Дмитрии Александровиче"
}
},
{
"employeeUID": "3a8efaed-90c2-11ed-80bb-000c29565159",
"personnelNumber": "ГОЗК-00342",
"employeeINN": "470304852482",
"employeeGender": "M",
"employeeName": "Яковлев Алексей Сергеевич",
"employeeLastName": "Яковлев",
"employeeFirstName": "Алексей",
"employeePatronymic": "Сергеевич",
"employeePhone": "+7 (981) 797-23-68",
"employee1CPostUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"individual1CCode": "ЗК-0000310",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"birthday": "1978-01-05",
"dateReceived": "2023-01-11",
"dismissalDate": "",
"inflection": {
"nominative": "Яковлев Алексей Сергеевич",
"genitive": "Яковлева Алексея Сергеевича",
"dative": "Яковлеву Алексею Сергеевичу",
"accusative": "Яковлева Алексея Сергеевича",
"ablative": "Яковлевым Алексеем Сергеевичем",
"prepositional": "Яковлеве Алексее Сергеевиче"
}
},
{
"employeeUID": "dfc4736f-8530-11ed-80bb-000c29565159",
"personnelNumber": "ГОЗК-00339",
"employeeINN": "519091229948",
"employeeGender": "M",
"employeeName": "Тавабилов Рамиль Салаватович",
"employeeLastName": "Тавабилов",
"employeeFirstName": "Рамиль",
"employeePatronymic": "Салаватович",
"employeePhone": "+7 (902) 1314414",
"employee1CPostUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"individual1CCode": "ЗК-0000307",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"birthday": "1997-04-16",
"dateReceived": "2023-01-10",
"dismissalDate": "",
"inflection": {
"nominative": "Тавабилов Рамиль Салаватович",
"genitive": "Тавабилова Рамиля Салаватовича",
"dative": "Тавабилову Рамилю Салаватовичу",
"accusative": "Тавабилова Рамиля Салаватовича",
"ablative": "Тавабиловым Рамилем Салаватовичем",
"prepositional": "Тавабилове Рамиле Салаватовиче"
}
},
{
"employeeUID": "a6277755-852b-11ed-80bb-000c29565159",
"personnelNumber": "ГОЗК-00338",
"employeeINN": "110210302127",
"employeeGender": "M",
"employeeName": "Шатков Юрий Павлович",
"employeeLastName": "Шатков",
"employeeFirstName": "Юрий",
"employeePatronymic": "Павлович",
"employeePhone": "+7 (994) 7236400",
"employee1CPostUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"employeeAdditional1CPostUID": "",
"employee1CSubdivisionUID": "7a0656d7-dfe6-11e7-80c3-00155d4c1e00",
"individual1CCode": "ЗК-0000306",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"birthday": "1982-08-16",
"dateReceived": "2023-01-10",
"dismissalDate": "",
"inflection": {
"nominative": "Шатков Юрий Павлович",
"genitive": "Шаткова Юрия Павловича",
"dative": "Шаткову Юрию Павловичу",
"accusative": "Шаткова Юрия Павловича",
"ablative": "Шатковым Юрием Павловичем",
"prepositional": "Шаткове Юрии Павловиче"
}
}
]
}', false);

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
                $user = User::withoutGlobalScopes()
                    ->where('first_name', '=', trim($employee->employeeFirstName))
                    ->where('last_name', '=', trim($employee->employeeLastName))
                    ->where('patronymic', '=', trim($employee->employeePatronymic))
                    ->where('birthday', '=', trim($formattedBirthday))
                    ->get()
                    ->first();

                // Need to use that after first sync
                /*$user = User::withoutGlobalScopes()
                    ->where('inn', '=', trim($employee->employeeINN))
                    ->first()
                    ->get();*/

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

                Log::channel('stderr')->info(var_dump($user));

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

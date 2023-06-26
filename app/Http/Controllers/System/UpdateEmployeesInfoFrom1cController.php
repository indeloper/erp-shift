<?php

namespace App\Http\Controllers\System;


use App\Models\Company\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\Employees\Employees1cSubdivision;
use App\Models\Employees\Employees1cPostInflection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;
use Telegram\Bot\Laravel\Facades\Telegram;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class UpdateEmployeesInfoFrom1cController extends Controller
{
    function uploadData(Request $request)  
    {
        // Проверка токена временно здесь, т.к. другие способы подразумевают аутентификацию пользователя. 
        // На первом решили действовать без перенастройки на стороне 1с
        if(empty($request->input('apiKey')) || $request->input('apiKey') != config('auth.SYNC_1C_API_KEY'))
        return response()->json([
            'result' => 'forbidden',
        ], 403);

        if($request->input('apiAction') == 'employeesSync')
        $result = $this->employeesSync($request->input('employeeList'));

        if($request->input('apiAction') == 'subdivisionsSync')
        $result = $this->subdivisionsSync($request->input('subdivisionList'));

        if($request->input('apiAction') == 'postsSync')
        $result = $this->postsSync($request->input('postList'));

        return response()->json([
            'result' => 'ok',
        ], 200);

    }

    function employeesSync($employeeList)
    {
        $notificationRecipients = User::where('is_su', 1)->get();   

        foreach ($employeeList as $employee) {

            $employee = (object)$employee;
            
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
                    ->where('inn', '=', trim($employee->employeeINN))
                    ->get()
                    ->first();

                    if (isset($user))
                    {
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
                    }

                $user = User::withoutGlobalScopes()
                    ->where('inn', '=', trim($employee->employeeINN))
                    ->get()
                    ->first();

                $lastEmployeesId = Employee::orderByDesc('id')->first()->id;
                
                $updated_employee = Employee::updateOrCreate(
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
                
                if($updated_employee->id > $lastEmployeesId){
                    foreach($notificationRecipients as $recipient){
                        Notification::create([
                            'name' => 'Добавлен сотрудник: ' . $employee->employeeName . ' ' . $updated_employee->employee1cPost->name,
                            'user_id' => $recipient->id,
                            'type' => 0,
                        ]);
                    }
                }

            }
        }
        
    }


    function subdivisionsSync($subdivisionList)
    {
        foreach ($subdivisionList as $subdivision) {

            $subdivision = (object)$subdivision;

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


    function postsSync($postList)
    {
        foreach ($postList as $post) {

            $post = (object)$post;

            if (empty($post->postUID))
            {
                continue;
            }

            $company = Company::where('company_1c_uid', $post->organizationUID)->get()->first();
            if (isset($company)) {
                $employeePost = Employees1cPost::updateOrCreate(
                    [
                        'post_1c_uid' => $post->postUID,
                    ],
                    [
                        "name" => $post->postName,
                        "company_id" => $company->id
                    ]
                );

                $post->inflection = (object)$post->inflection;

                    Log::channel('stderr')->info('[info] ' . $post->inflection->nominative);
                    Employees1cPostInflection::updateOrCreate(
                        [
                            'post_id' => $employeePost->id
                        ],
                        [
                            'nominative' => $post->inflection->nominative,
                            'genitive' => $post->inflection->genitive,
                            'dative' => $post->inflection->dative,
                            'accusative' => $post->inflection->accusative,
                            'ablative' => $post->inflection->ablative,
                            'prepositional' => $post->inflection->prepositional
                        ]
                    );

            }
        }
    }


}

<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\Employees\Employees1cPostInflection;
use App\Models\Employees\Employees1cSubdivision;
use App\Models\User;
use App\Notifications\Employee\EmployeeTerminationNotice;
use App\Notifications\Employee\NewEmployeeArrivalNotice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function morphos\Russian\pluralize;

class UpdateEmployeesInfoFrom1cController extends Controller
{
    public function uploadData(Request $request): JsonResponse
    {
        // Проверка токена временно здесь, т.к. другие способы подразумевают аутентификацию пользователя.
        // На первом решили действовать без перенастройки на стороне 1с

        $jsonData = json_decode($request->getContent());

        if (empty($jsonData->apiKey) || $jsonData->apiKey != config('auth.SYNC_1C_API_KEY')) {
            return response()->json([
                'result' => 'Forbidden. API Key is not valid',
            ], 403);
        }

        if ($jsonData->apiAction == 'subdivisionsSync') {
            $this->subdivisionsSync($jsonData->subdivisionList);
        }

        if ($jsonData->apiAction == 'postsSync') {
            $this->postsSync($jsonData->postList);
        }

        if ($jsonData->apiAction == 'employeesSync') {
            $this->employeesSync($jsonData->employeeList);
        }

        return response()->json([
            'result' => 'ok',
        ], 200);

    }

    public function subdivisionsSync($subdivisionList)
    {
        foreach ($subdivisionList as $subdivision) {
            $company = Company::where('company_1c_uid', $subdivision->organizationUID)->get()->first();

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

    public function postsSync($postList)
    {
        foreach ($postList as $post) {

            $post = (object) $post;

            if (empty($post->postUID)) {
                continue;
            }

            $company = Company::where('company_1c_uid', $post->organizationUID)->get()->first();
            if (isset($company)) {
                $employeePost = Employees1cPost::updateOrCreate(
                    [
                        'post_1c_uid' => $post->postUID,
                    ],
                    [
                        'name' => $post->postName,
                        'company_id' => $company->id,
                    ]
                );

                $post->inflection = (object) $post->inflection;
                Employees1cPostInflection::updateOrCreate(
                    [
                        'post_id' => $employeePost->id,
                    ],
                    [
                        'nominative' => $post->inflection->nominative,
                        'genitive' => $post->inflection->genitive,
                        'dative' => $post->inflection->dative,
                        'accusative' => $post->inflection->accusative,
                        'ablative' => $post->inflection->ablative,
                        'prepositional' => $post->inflection->prepositional,
                    ]
                );

            }
        }
    }

    public function employeesSync($employeeList)
    {
        $notificationRecipients = User::where('is_su', 1)->get();

        foreach ($employeeList as $employee) {
            $employee = (object) $employee;

            $company = Company::where('company_1c_uid', $employee->organizationUID)->get()->first();
            $employeePost = Employees1cPost::where('post_1c_uid', '=', $employee->employee1CPostUID)->get()->first();
            $employeeSubdivision = Employees1cSubdivision::where('subdivision_1c_uid', '=', $employee->employee1CSubdivisionUID)->get()->first();
            $formattedBirthday = Carbon::parse($employee->birthday)->format('d.m.Y');
            $formattedPhone = preg_replace('/[^0-9]/', '', trim($employee->employeePhone));
            if (substr($formattedPhone, 0, 1) == 8) {
                $formattedPhone = substr_replace($formattedPhone, '7', 0, 1);
            }

            $user = User::withoutGlobalScopes()
                ->where('INN', trim($employee->employeeINN))
                ->first();

            if ($user) {
                $user->update([
                    'first_name' => trim($employee->employeeFirstName),
                    'last_name' => trim($employee->employeeLastName),
                    'patronymic' => trim($employee->employeePatronymic),
                    'birthday' => $formattedBirthday,
                    'gender' => trim($employee->employeeGender),
                ]);
            } else {
                $user = User::create([
                    'first_name' => trim($employee->employeeFirstName),
                    'last_name' => trim($employee->employeeLastName),
                    'patronymic' => trim($employee->employeePatronymic),
                    'birthday' => $formattedBirthday,
                    'email' => null,
                    'person_phone' => $formattedPhone,
                    'department_id' => 0,
                    'group_id' => 0,
                    'INN' => trim($employee->employeeINN),
                    'gender' => trim($employee->employeeGender),
                ]);
            }

            $existingEmployee = Employee::where('employee_1c_uid', $employee->employeeUID)->first();
            if ($existingEmployee) {
                if (Carbon::parse($employee->dismissalDate) == Carbon::today()) {
                    $dismissedEmployeesList[] = (object) [
                        'userId' => $user->id,
                        'fullName' => $employee->employeeName,
                        'postName' => $employeePost->name,
                        'companyName' => $company->name,
                        'birthday' => Carbon::parse($employee->birthday)->format('d.m.Y'),
                    ];
                }
            } else {
                $newEmployeesList[] = (object) [
                    'userId' => $user->id,
                    'fullName' => $employee->employeeName,
                    'postName' => $employeePost->name,
                    'companyName' => $company->name,
                    'birthday' => Carbon::parse($employee->birthday)->format('d.m.Y'),
                ];
            }

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
                    'report_group_id' => null,
                ]
            );

            $actualEmployee = Employee::where('user_id', $user->id)
                ->where(function ($query) {
                    $query->where('dismissal_date', '>=', date('Y-m-d'))
                        ->orWhere('dismissal_date', '=', '0000-00-00');
                })
                ->orderBy('company_id')
                ->first();

            $userStatus = $actualEmployee ? 1 : 0;
            $actualCompanyId = $actualEmployee ? $actualEmployee->company_id : $user->company_id;

            $user->update(
                [
                    'company' => $actualCompanyId,
                    'status' => $userStatus,
                ]
            );
        }

        if (isset($newEmployeesList)) {
            $newEmployeesNotificationMessageText = '<b>'.pluralize(count($newEmployeesList), 'новый сотрудник').':</b>';
            foreach ($newEmployeesList as $newEmployee) {
                $userCardUrl = route('users::card', $newEmployee->userId);
                $newEmployeesNotificationMessageText .= "\n<a href='{$userCardUrl}'>{$newEmployee->fullName}</a> ({$newEmployee->birthday} г.р) — {$newEmployee->postName}, {$newEmployee->companyName}";
            }

            foreach ($notificationRecipients as $recipient) {
                NewEmployeeArrivalNotice::send(
                    $recipient->id,
                    [
                        'name' => $newEmployeesNotificationMessageText,
                    ]
                );
            }
        }

        if (isset($dismissedEmployeesList)) {
            $dismissedEmployeesNotificationMessageText = '<b>'.pluralize(count($dismissedEmployeesList), 'уволенный сотрудник').':</b>';
            foreach ($dismissedEmployeesList as $dismissedEmployee) {
                $userCardUrl = route('users::card', $dismissedEmployee->userId);
                $dismissedEmployeesNotificationMessageText .= "\n<a href='{$userCardUrl}'>{$dismissedEmployee->fullName}</a> ({$dismissedEmployee->birthday} г.р) — {$dismissedEmployee->postName}, {$dismissedEmployee->companyName}";
            }
            foreach ($notificationRecipients as $recipient) {
                EmployeeTerminationNotice::send(
                    $recipient->id,
                    [
                        'name' => $dismissedEmployeesNotificationMessageText,
                    ]
                );
            }
        }
    }
}

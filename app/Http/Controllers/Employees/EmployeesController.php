<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use App\Models\Employees\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeesController extends Controller
{
    /**
     * Display a view of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response\Illuminate\View\View
     */
    public function index()
    {

    }

    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = json_decode($request['loadOptions']);

        return (new Employee())
            ->dxLoadOptions($loadOptions)
            ->leftJoin('companies', 'employees.company_id', '=', 'companies.id')
            ->leftJoin('employees_1c_posts', 'employees.employee_1c_post_id', '=', 'employees_1c_posts.id')
            ->orderBy('employee_1c_name')
            ->get(
                [
                    'employees.id',
                    'employee_1c_name',
                    'companies.id as company_id',
                    'companies.name as company_name',
                    'employees_1c_posts.name as post_name',
                    DB::Raw("CONCAT(`employee_1c_name`, ' (', `companies`.`name`, ' | ', `employees_1c_posts`.`name`, ')') as `employee_extended_name`")
                ]
            )
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {

    }
}

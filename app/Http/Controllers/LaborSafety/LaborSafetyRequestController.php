<?php

namespace App\Http\Controllers\LaborSafety;

use App\Models\Company\Company;
use App\Models\Company\CompanyReportTemplate;
use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyOrderWorker;
use App\Models\LaborSafety\LaborSafetyRequest;
use App\Models\LaborSafety\LaborSafetyRequestOrder;
use App\Models\LaborSafety\LaborSafetyRequestStatus;
use App\Models\LaborSafety\LaborSafetyRequestWorker;
use App\Models\OneC\Employee;
use App\Models\OneC\Employees1cPost;
use App\Models\ProjectObject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class LaborSafetyRequestController extends Controller
{
    //const PAGE_BREAK_DELIMITER = '<br style="page-break-after: always"/>';
    //const PAGE_BREAK_DELIMITER = '&#12';
    const PAGE_BREAK_DELIMITER = '<pagebreak></pagebreak>'; // Needs to modify vendor component https://github.com/PHPOffice/PHPWord/issues/1601


    /**
     * Display a view of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response\Illuminate\View\View
     */
    public function index()
    {
        return view('labor-safety.labor-safety-orders-and-requests');
    }

    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = json_decode($request['loadOptions']);

        $query = (new LaborSafetyRequest())
            ->dxLoadOptions($loadOptions);

        if (!Auth::user()->can('labor_safety_order_list_access') || !Auth::user()->is_su) {
            $query->where('author_user_id', '=', Auth::id());
        }

        return $query
            ->get()
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY);

        $data["author_user_id"] = Auth::id();
        $data["request_status_id"] = 1;

        if (isset($data["workers"])) {
            $workers = $data["workers"];
            unset($data["workers"]);
        }

        DB::beginTransaction();

        $laborSafetyRequestRow = new LaborSafetyRequest($data);

        $laborSafetyRequestRow->save();

        if (isset($workers)) {
            foreach ($workers as $worker) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $worker["worker_employee_id"]
                    ]
                );
                $newWorker->save();
            }
        }

        DB::commit();
        return response()->json([
            'result' => 'ok',
            'key' => $laborSafetyRequestRow->id
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->all()["key"];

        $modifiedData = json_decode($request->all()["modifiedData"], JSON_OBJECT_AS_ARRAY);
        $orders = $modifiedData["ordersData"];

        $generateOrders = $modifiedData["perform_orders"];

        unset($modifiedData["ordersData"]);
        unset($modifiedData["perform_orders"]);

        $requestRow = LaborSafetyRequest::findOrFail($id);

        DB::beginTransaction();

        $this->insertOrUpdateOrdersData($orders, $id);

        if ($generateOrders) {
            $modifiedData["generated_html"] = $this->generateRequestHtmlData($requestRow);
            $modifiedData["request_status_id"] = 2;
            $modifiedData["implementer_user_id"] = Auth::id();
        }

        $requestRow->update($modifiedData);

        DB::commit();
        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    public function generateRequestHtmlData($request)
    {
        $orders = LaborSafetyRequestOrder::join('labor_safety_order_types', 'labor_safety_request_orders.order_type_id', '=', 'labor_safety_order_types.id')
            ->where('labor_safety_request_orders.request_id', '=', $request->id)
            ->where('labor_safety_request_orders.include_in_formation', '=', 1)
            ->get([
                'labor_safety_request_orders.id',
                'labor_safety_request_orders.request_id',
                'labor_safety_request_orders.order_type_id',
                'labor_safety_request_orders.responsible_employee_id',
                'labor_safety_request_orders.sub_responsible_employee_id',
                'labor_safety_request_orders.include_in_formation',
                'labor_safety_order_types.order_type_category_id',
                'labor_safety_order_types.name',
                'labor_safety_order_types.short_name',
                'labor_safety_order_types.full_name',
                'labor_safety_order_types.template'
            ]);

        $resultHtml = '';

        foreach ($orders as $order) {

            $orderTemplate = $this->fillTemplateData($request, $order, $order->template);

            $resultHtml .= $this->getCompanyHeaderTemplateWithData($request) . $orderTemplate . self::PAGE_BREAK_DELIMITER;
        }

        return $resultHtml;
    }

    function fillTemplateData($request, $order, $orderTemplate)
    {
        $variables = $this->getArrayOfTemplateVariables($orderTemplate);
        $projectObject = ProjectObject::find($request->project_object_id);
        $responsibleEmployee = Employee::find($order->responsible_employee_id);
        $subResponsibleEmployee = Employee::find($order->sub_responsible_employee_id);
        $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

        $prettyOrderDate = Carbon::parse($request->order_date)->format('«d»') .
            ' ' .
            $months[Carbon::parse($request->order_date)->format('n') - 1] .
            ' ' .
            Carbon::parse($request->order_date)->format('Y г.');

        foreach ($variables as $variable) {
            switch ($variable) {
                case "{request_id}":
                    $orderTemplate = str_replace($variable, $request->id, $orderTemplate);
                    break;
                case "{template_short_name}":
                    $orderTemplate = str_replace($variable, $order->short_name, $orderTemplate);
                    break;
                case "{order_date}":
                    $orderTemplate = str_replace($variable, Carbon::parse($request->order_date)->format('d.m.Y'), $orderTemplate);
                    break;
                case "{pretty_order_date}":
                    $orderTemplate = str_replace($variable, $prettyOrderDate, $orderTemplate);
                    break;
                case "{responsible_employee_name_initials_before}":
                case "{responsible_employee_name_initials_after}":
                case "{responsible_employee_full_name}":
                    if (isset($responsibleEmployee)) {
                        $orderTemplate = str_replace($variable, $responsibleEmployee->employee_1c_name, $orderTemplate);
                    }
                    break;
                case "{responsible_employee_post}":
                    if (isset($responsibleEmployee)) {
                        $employeePostName = Employees1cPost::find($responsibleEmployee->employee_1c_post_id)->name;
                        $orderTemplate = str_replace($variable, $employeePostName, $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_name_initials_after}":
                case "{subresponsible_employee_name_initials_before}":
                case "{subresponsible_employee_full_name}":
                    if (isset($subResponsibleEmployee)) {
                        $orderTemplate = str_replace($variable, $subResponsibleEmployee->employee_1c_name, $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_post}":
                    if (isset($subResponsibleEmployee)) {
                        $employeePostName = Employees1cPost::find($subResponsibleEmployee->employee_1c_post_id)->name;
                        $orderTemplate = str_replace($variable, $employeePostName, $orderTemplate);
                    }
                    break;
                case "{project_object_name}":
                    $orderTemplate = str_replace($variable, $projectObject->name, $orderTemplate);
                    break;
                case "{project_object_full_address}":
                    $orderTemplate = str_replace($variable, $projectObject->address, $orderTemplate);
                    break;
                case "{project_object_cadastral_number}":
                    $orderTemplate = str_replace($variable, $projectObject->cadastral_number, $orderTemplate);
                    break;
                case "{workers_list}":
                    $orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($order), $orderTemplate);
                    break;
                case "{sign_list}":
                    $this->getSignList($order);
                    break;
            }
        }

        if (!isset($responsibleEmployee)) {
            $pattern = '/\[optional-section-start\|subresponsible_employee].+\[optional-section-end\|subresponsible_employee]/';
            $orderTemplate = preg_replace($pattern, '', $orderTemplate);
        } else {
            $orderTemplate = str_replace(['[optional-section-start|subresponsible_employee]', '[optional-section-end|subresponsible_employee]'], '', $orderTemplate);
        }

        return $orderTemplate;
    }

    function getArrayOfTemplateVariables($template): array
    {
        $variables = [];

        preg_match_all('/\{(.)+?\}/', $template, $variables);
        return array_unique($variables[0]);
    }

    function getWorkersListForTemplate($order)
    {
        $workersList = '<ol style="list-style-type: disc;">';

        $workers = LaborSafetyOrderWorker::where('request_order_id', '=', $order->id)->get();

        foreach ($workers as $worker) {
            $employeeId = $worker->worker_employee_id;
            $employee = Employee::find($employeeId);
            $postName = Employees1cPost::find($employee->employee_1c_post_id)->name;

            $workersList .= '<li>' . $postName . ' – ' . $employee->employee_1c_name . '</li>';
        }

        $workersList .= '</ol>';
        return $workersList;
    }

    function getSignList($order)
    {
        $signList = '<table style="width: 100%; height: 28px;"><tbody>';

        if (isset($order->responsible_employee_id)) {
            $employeeName = Employee::find($order->responsible_employee_id)->employee_1c_name;
            $signList .= '<tr style="height: 76px;"><td style="width: 33%; height: 10px;"><p>С&nbsp;приказом&nbsp;ознакомлен:</p></td><td style="width: 33%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td><td style="height: 10px; width: 33%;"><p style="text-align: right;">' . $employeeName . '</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr>';
        }

        if (isset($order->sub_responsible_employee_id)) {
            $employeeName = Employee::find($order->sub_responsible_employee_id)->employee_1c_name;
            $signList .= '<tr style="height: 76px;"><td style="width: 33%; height: 10px;"><p>С&nbsp;приказом&nbsp;ознакомлен:</p></td><td style="width: 33%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td><td style="height: 10px; width: 33%;"><p style="text-align: right;">' . $employeeName . '</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr>';
        }

        $workers = LaborSafetyOrderWorker::where('request_order_id', '=', $order->id)->get();

        foreach ($workers as $worker) {
            $employeeName = Employee::find($worker->worker_employee_id)->employee_1c_name;
            $signList .= '<tr style="height: 76px;"><td style="width: 33%; height: 10px;"><p>С&nbsp;приказом&nbsp;ознакомлен:</p></td><td style="width: 33%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td><td style="height: 10px; width: 33%;"><p style="text-align: right;">' . $employeeName . '</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr>';
        }

        $signList .= '</tbody></table>';

        return $signList;
    }

    function getCompanyHeaderTemplateWithData($request)
    {
        $companyTemplate = CompanyReportTemplate::where('company_id', '=', $request->company_id)
            ->where('template_type', '=', 1)
            ->first()
            ->template;

        $company = Company::find($request->company_id);

        $variables = $this->getArrayOfTemplateVariables($companyTemplate);

        foreach ($variables as $variable) {
            switch ($variable) {
                case "{company_legal_address}":
                    $companyTemplate = str_replace($variable, $company->legal_address, $companyTemplate);
                    break;
                case "{company_phone}":
                    $companyTemplate = str_replace($variable, $company->phone, $companyTemplate);
                    break;
                case "{company_web_site}":
                    $companyTemplate = str_replace($variable, $company->web_site, $companyTemplate);
                    break;
                case "{company_email}":
                    $companyTemplate = str_replace($variable, $company->email, $companyTemplate);
                    break;
            }
        }

        return $companyTemplate;
    }

    public function getRequestWorkers(Request $request)
    {
        $requestId = json_decode($request['requestId']);

        $request = LaborSafetyRequest::find($requestId);
        $responsibleEmployees = [];

        if (Auth::user()->can('labor_safety_generate_documents_access')) {
            $responsibleEmployees[] = [
                'id' => (string)Str::uuid(),
                'worker_employee_id' => $request->responsible_employee_id,
                'employee_role' => 'Ответственный'
            ];

            if (isset($request->sub_responsible_employee_id)) {
                $responsibleEmployees[] = [
                    'id' => (string)Str::uuid(),
                    'worker_employee_id' => $request->responsible_employee_id,
                    'employee_role' => 'Замещающий ответственного'
                ];
            }
        }

        $workers = LaborSafetyRequestWorker::where('request_id', '=', $requestId)
            ->leftJoin('labor_safety_order_workers', 'labor_safety_request_workers.id', '=', 'labor_safety_order_workers.requests_worker_id')
            ->get(
                [
                    'labor_safety_request_workers.id',
                    'labor_safety_request_workers.worker_employee_id',
                    DB::Raw("'Сотрудник' as `employee_role`")
                ]
            )
            ->toArray();

        $workers = array_merge($responsibleEmployees, $workers);

        $orderTypesList = LaborSafetyOrderType::all();

        foreach ($workers as $worker) {
            forEach($orderTypesList as $orderType) {

            }
        }

        return json_encode($workers);
    }

    public function statusesList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new LaborSafetyRequestStatus())
            ->dxLoadOptions($options)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    function download(Request $request)
    {
        $requestId = json_decode($request->input('requestId'));
        $html = LaborSafetyRequest::findOrFail($requestId)->generated_html;
        $html = str_replace('<br>', '<br/>', $html);
        $html = str_replace('<hr>', '<hr/>', $html);

        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        Html::addHtml($section, $html, false, false);

        $phpWord->save('File.docx', 'Word2007', true);
        exit;
    }
}

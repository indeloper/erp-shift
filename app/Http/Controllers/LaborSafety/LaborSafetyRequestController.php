<?php

namespace App\Http\Controllers\LaborSafety;

use App\Http\Requests\ProjectRequest\ProjectStatRequest;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Company\Company;
use App\Models\Company\CompanyReportTemplate;
use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyOrderWorker;
use App\Models\LaborSafety\LaborSafetyRequest;
use App\Models\LaborSafety\LaborSafetyRequestOrder;
use App\Models\LaborSafety\LaborSafetyRequestStatus;
use App\Models\LaborSafety\LaborSafetyRequestWorker;
use App\Models\LaborSafety\LaborSafetyWorkerType;
use App\Models\Notification;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\UserPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use morphos\English\NounPluralization;
use morphos\Russian\NounDeclension;
use PhpOffice\PhpWord\ComplexType\ProofState;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Language;
use function morphos\Russian\inflectName;

class LaborSafetyHtml extends Html
{
    /**
     * Parse a node and add a corresponding element to the parent element.
     *
     * @param \DOMNode $node node to parse
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element object to add an element corresponding with the node
     * @param array $styles Array with all styles
     * @param array $data Array to transport data to a next level in the DOM tree, for example level of listitems
     */
    protected static function parseNode($node, $element, $styles = array(), $data = array())
    {
        // Populate styles array
        $styleTypes = array('font', 'paragraph', 'list', 'table', 'row', 'cell');
        foreach ($styleTypes as $styleType) {
            if (!isset($styles[$styleType])) {
                $styles[$styleType] = array();
            }
        }

        // Node mapping table
        $nodes = array(
            // $method        $node   $element    $styles     $data   $argument1      $argument2
            'p' => array('Paragraph', $node, $element, $styles, null, null, null),
            'h1' => array('Heading', null, $element, $styles, null, 'Heading1', null),
            'h2' => array('Heading', null, $element, $styles, null, 'Heading2', null),
            'h3' => array('Heading', null, $element, $styles, null, 'Heading3', null),
            'h4' => array('Heading', null, $element, $styles, null, 'Heading4', null),
            'h5' => array('Heading', null, $element, $styles, null, 'Heading5', null),
            'h6' => array('Heading', null, $element, $styles, null, 'Heading6', null),
            '#text' => array('Text', $node, $element, $styles, null, null, null),
            'strong' => array('Property', null, null, $styles, null, 'bold', true),
            'b' => array('Property', null, null, $styles, null, 'bold', true),
            'em' => array('Property', null, null, $styles, null, 'italic', true),
            'i' => array('Property', null, null, $styles, null, 'italic', true),
            'u' => array('Property', null, null, $styles, null, 'underline', 'single'),
            'sup' => array('Property', null, null, $styles, null, 'superScript', true),
            'sub' => array('Property', null, null, $styles, null, 'subScript', true),
            'span' => array('Span', $node, null, $styles, null, null, null),
            'font' => array('Span', $node, null, $styles, null, null, null),
            'table' => array('Table', $node, $element, $styles, null, null, null),
            'tr' => array('Row', $node, $element, $styles, null, null, null),
            'td' => array('Cell', $node, $element, $styles, null, null, null),
            'th' => array('Cell', $node, $element, $styles, null, null, null),
            'ul' => array('List', $node, $element, $styles, $data, null, null),
            'ol' => array('List', $node, $element, $styles, $data, null, null),
            'li' => array('ListItem', $node, $element, $styles, $data, null, null),
            'img' => array('Image', $node, $element, $styles, null, null, null),
            'br' => array('WordBreak', null, $element, $styles, null, null, null),
            'a' => array('Link', $node, $element, $styles, null, null, null),
            'input' => array('Input', $node, $element, $styles, null, null, null),
            'hr' => array('HorizRule', $node, $element, $styles, null, null, null),
            'pagebreak' => array('PageBreak', null, $element, $styles, null, null, null),
        );

        $newElement = null;
        $keys = array('node', 'element', 'styles', 'data', 'argument1', 'argument2');

        if (isset($nodes[$node->nodeName])) {
            // Execute method based on node mapping table and return $newElement or null
            // Arguments are passed by reference
            $arguments = array();
            $args = array();
            list($method, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5]) = $nodes[$node->nodeName];
            for ($i = 0; $i <= 5; $i++) {
                if ($args[$i] !== null) {
                    $arguments[$keys[$i]] = &$args[$i];
                }
            }
            $method = "parse{$method}";
            $newElement = call_user_func_array(array('PhpOffice\PhpWord\Shared\Html', $method), array_values($arguments));

            // Retrieve back variables from arguments
            foreach ($keys as $key) {
                if (array_key_exists($key, $arguments)) {
                    $$key = $arguments[$key];
                }
            }
        }

        if ($newElement === null) {
            $newElement = $element;
        }

        static::parseChildNodes($node, $newElement, $styles, $data);
    }

    /**
     * Parse page break
     *
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element
     */
    protected static function parsePageBreak($element)
    {
        $element->addPageBreak();
    }
}

class LaborSafetyRequestController extends Controller
{
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

        if (!Auth::user()->can('labor_safety_order_list_access') && !Auth::user()->is_su) {
            $query->where('author_user_id', '=', Auth::id());
        }

        return $query
            ->get(
                [
                    'id',
                    'order_number',
                    'order_date',
                    'company_id',
                    'project_object_id',
                    'author_user_id',
                    'implementer_user_id',
                    'responsible_employee_id',
                    'sub_responsible_employee_id',
                    'request_status_id',
                    DB::raw("IF(ISNULL(`generated_html`) OR `generated_html` = '', 0, 1) as `is_orders_generated`"),
                    'comment'
                ]
            )
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
            if (isset($laborSafetyRequestRow->responsible_employee_id)) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $laborSafetyRequestRow->responsible_employee_id,
                        'worker_type_id' => 1
                    ]
                );
                $newWorker->save();
            }

            if (isset($laborSafetyRequestRow->sub_responsible_employee_id)) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $laborSafetyRequestRow->sub_responsible_employee_id,
                        'worker_type_id' => 2
                    ]
                );
                $newWorker->save();
            }

            foreach ($workers as $worker) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $worker["worker_employee_id"],
                        'worker_type_id' => 3
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
        $workers = $modifiedData["workers"];
        $editAction = $modifiedData["editAction"];

        unset($modifiedData["workers"]);
        unset($modifiedData["perform_orders"]);
        unset($modifiedData["editAction"]);

        $requestRow = LaborSafetyRequest::findOrFail($id);

        DB::beginTransaction();

        LaborSafetyOrderWorker::where('request_id', '=', $id)->forceDelete();
        LaborSafetyRequestWorker::where('request_id', '=', $id)->forceDelete();


        if (isset($workers)) {
            foreach ($workers as $worker) {
                $requestWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $id,
                        'worker_employee_id' => $worker['worker_employee_id'],
                        'worker_type_id' => $worker['employee_role_id']
                    ]
                );
                $requestWorker->save();

                if (isset($worker['orders'])) {
                    foreach ($worker['orders'] as $orderType) {
                        $orderWorker = new LaborSafetyOrderWorker([
                            'request_id' => $id,
                            'order_type_id' => $orderType,
                            'requests_worker_id' => $requestWorker->id
                        ]);
                        $orderWorker->save();
                    }
                }
            }
        }

        $requestRow->update($modifiedData);

        $generateOrders = Auth::user()->can('labor_safety_generate_documents_access');

        if ($generateOrders) {
            $modifiedData["generated_html"] = $this->generateRequestHtmlData($requestRow);
            $modifiedData["request_status_id"] = 2;
            $modifiedData["implementer_user_id"] = Auth::id();
        };

        switch ($editAction) {
            case "cancelRequest":
                $modifiedData["request_status_id"] = 3;
                break;
            case "completeRequest":
                $modifiedData["request_status_id"] = 4;
                break;
        }

        $requestRow->update($modifiedData);

        DB::commit();

        $this->sendRequestNotification($requestRow);
        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    public function generateRequestHtmlData($request)
    {
        $orders = LaborSafetyOrderWorker::join('labor_safety_order_types', 'labor_safety_order_workers.order_type_id', '=', 'labor_safety_order_types.id')
            ->where('labor_safety_order_workers.request_id', '=', $request->id)
            ->distinct()
            ->orderBy('labor_safety_order_workers.order_type_id')
            ->get([
                'labor_safety_order_workers.order_type_id',
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

        $coverLetter = LaborSafetyOrderType::find(26);
        $orderTemplate = $this->fillTemplateData($request, $coverLetter, $coverLetter->template);
        $resultHtml .= $this->getCompanyHeaderTemplateWithData($request) . $orderTemplate;

        return $resultHtml;
    }

    /**
     * @throws \Exception
     */
    function fillTemplateData($request, $order, $orderTemplate)
    {
        $variables = $this->getArrayOfTemplateVariables($orderTemplate);
        $projectObject = ProjectObject::find($request->project_object_id);

        $responsibleEmployee = Employee::find(LaborSafetyRequestWorker::where('request_id', '=', $request->id)
            ->where('worker_type_id', '=', 1)
            ->get()
            ->first()
            ->worker_employee_id);

        $subResponsibleWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
            ->where('worker_type_id', '=', 2)
            ->get()
            ->first();
        if (isset($subResponsibleWorker)) {
            $subResponsibleEmployee = Employee::find($subResponsibleWorker->worker_employee_id);
        }

        $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

        $prettyOrderDate = Carbon::parse($request->order_date)->format('«d»') .
            ' ' .
            $months[Carbon::parse($request->order_date)->format('n') - 1] .
            ' ' .
            Carbon::parse($request->order_date)->format('Y г.');

        $orderTemplate = str_replace('<p>{sign_list}</p>', '{sign_list}', $orderTemplate);

        foreach ($variables as $variable) {
            switch ($variable) {
                case "{request_id}":
                    $orderTemplate = str_replace($variable, $request->order_number, $orderTemplate);
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
                    if (isset($responsibleEmployee)) {
                        $responsibleEmployeeName = $responsibleEmployee->format('f. p. L', 'именительный');
                        $orderTemplate = str_replace($variable, $responsibleEmployeeName, $orderTemplate);
                    }
                    break;
                case "{responsible_employee_name_initials_after}":
                    if (isset($responsibleEmployee)) {
                        $responsibleEmployeeName = $responsibleEmployee->format('L f. p.', 'винительный');
                        $orderTemplate = str_replace($variable, $responsibleEmployeeName, $orderTemplate);
                    }
                    break;
                case "{responsible_employee_full_name}":
                    if (isset($responsibleEmployee)) {
                        $responsibleEmployeeName = $responsibleEmployee->format('L F P', 'винительный');
                        $orderTemplate = str_replace($variable, $responsibleEmployeeName, $orderTemplate);
                    }
                    break;
                case "{responsible_employee_post}":
                    if (isset($responsibleEmployee)) {
                        $employeePostName = $this->mb_lcfirst(Employees1cPost::find($responsibleEmployee->employee_1c_post_id)->getInflection('винительный'));
                        $orderTemplate = str_replace($variable, $employeePostName, $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_name_initials_after}":
                    if (isset($subResponsibleEmployee)) {
                        $subResponsibleEmployeeName = $subResponsibleEmployee->format('L f. p.', 'винительный');
                        $orderTemplate = str_replace($variable, $subResponsibleEmployeeName, $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_name_initials_before}":
                    if (isset($subResponsibleEmployee)) {
                        $subResponsibleEmployeeName = $subResponsibleEmployee->format('f. p. L', 'именительный');
                        $orderTemplate = str_replace($variable, $subResponsibleEmployeeName, $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_full_name}":
                    if (isset($subResponsibleEmployee)) {
                        $subResponsibleEmployeeName = $subResponsibleEmployee->format('L F P', 'винительный');
                        $orderTemplate = str_replace($variable, $subResponsibleEmployeeName, $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_post}":
                    if (isset($subResponsibleEmployee)) {
                        $employeePostName = $this->mb_lcfirst(Employees1cPost::find($subResponsibleEmployee->employee_1c_post_id)->getInflection('винительный'));
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
                    if (!empty($projectObject->cadastral_number)){
                       $orderTemplate = str_replace($variable, ', на земельном участке с кадастровым номером ' . $projectObject->cadastral_number, $orderTemplate);
                    } else {
                       $orderTemplate = str_replace($variable, '', $orderTemplate);
                    }
                    break;
                case "{object_responsible_users}":
                    $objectResponsibleEmployees = $this->getObjectResponsibleEmployees($request);
                    $objectResponsibleEmployeesHtml = '';
                    foreach ($objectResponsibleEmployees as $objectResponsibleEmployee) {
                        $objectResponsibleEmployeePost = Employees1cPost::find($objectResponsibleEmployee->employee_1c_post_id)->name;
                        $objectResponsibleEmployeesHtml .= ' — ' . $objectResponsibleEmployeePost . ' ' . $objectResponsibleEmployee->format('L F P', 'родительный');
                        $objectResponsibleEmployeesHtml .= '<br/>';
                    }
                    $objectResponsibleEmployeesHtml = trim($objectResponsibleEmployeesHtml, '<br/>');
                    $orderTemplate = str_replace($variable, $objectResponsibleEmployeesHtml, $orderTemplate);
                    break;
                case "{contractor_name}":
                    $contractor = Project::where("object_id", '=', $request->project_object_id)
                        ->leftJoin('contractors', 'projects.contractor_id', '=', 'contractors.id')
                        ->get(['contractors.short_name'])
                        ->first();

                    $orderTemplate = str_replace($variable, $contractor->short_name, $orderTemplate);
                    break;
                case "{company_name}":
                    $company = Company::find($request->company_id);

                    $orderTemplate = str_replace($variable, $company->name, $orderTemplate);
                    break;
                case "{object_responsible_employee_post}":
                    //$orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($request, $order), $orderTemplate);
                    break;
                case "{main_labor_safety_employee_post}":
                    //$orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($request, $order), $orderTemplate);
                    break;
                case "{main_labor_safety_employee_full_name}":
                    //$orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($request, $order), $orderTemplate);
                    break;
                case "{main_labor_safety_employee_phone}":
                    //$orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($request, $order), $orderTemplate);
                    break;
                case "{responsible_engineer_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 6)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleEngineerEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);

                        $responsibleEngineerEmployeePost = Employees1cPost::find($responsibleEngineerEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($responsibleEngineerEmployeePost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{responsible_engineer_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 6)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleEngineerEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                        $orderTemplate = str_replace($variable, $responsibleEngineerEmployee->format('L F P', 'винительный'), $orderTemplate);
                    }
                    break;
                case "{responsible_labor_safety_employee_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 7)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleLaborSafetyEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);

                        $responsibleLaborSafetyEmployeePost = Employees1cPost::find($responsibleLaborSafetyEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($responsibleLaborSafetyEmployeePost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{responsible_labor_safety_employee_full_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 7)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $gasWeldingWorksEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                        $orderTemplate = str_replace($variable, $gasWeldingWorksEmployee->format('L F P', 'винительный'), $orderTemplate);
                    }
                    break;
                case "{gas_welding_works_employee_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 8)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $gasWeldingWorksEmployee = Employee::find(LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                            ->where('worker_type_id', '=', 8)
                            ->first()
                            ->worker_employee_id);

                        $gasWeldingWorksEmployeePost = Employees1cPost::find($gasWeldingWorksEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($gasWeldingWorksEmployeePost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{gas_welding_works_employee_full_name}":
                    $gasWeldingWorksEmployee = Employee::find(LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 8)
                        ->first()
                        ->worker_employee_id);
                    $orderTemplate = str_replace($variable, $gasWeldingWorksEmployee->format('L F P', 'винительный'), $orderTemplate);
                    break;
                case "{gas_welding_works_employee_certificate}":
                    //$orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($request, $order), $orderTemplate);
                    break;
                case "{workers_list}":
                    $orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($request, $order), $orderTemplate);
                    break;
                case "{sign_list}":
                    $orderTemplate = str_replace($variable, $this->getSignList($request, $order), $orderTemplate);
                    break;
                case "{generated_orders_list}":
                    $orderTemplate = str_replace($variable, $this->getOrdersList($request), $orderTemplate);
            }
        }

        if (isset($subResponsibleEmployee) and ($this->isEmployeeParticipatesInOrder($request->id, $subResponsibleEmployee->id, $order->order_type_id))) {
            $orderTemplate = str_replace(['[optional-section-start|subresponsible_employee]', '[optional-section-end|subresponsible_employee]'], '', $orderTemplate);
        } else {
            $pattern = '/\[optional-section-start\|subresponsible_employee].*?\[optional-section-end\|subresponsible_employee]/s';
            $orderTemplate = preg_replace($pattern, '', $orderTemplate);
        }

        return $orderTemplate;
    }

    function getArrayOfTemplateVariables($template): array
    {
        $variables = [];

        preg_match_all('/\{(.)+?\}/', $template, $variables);
        return array_unique($variables[0]);
    }

    private function mb_lcfirst($string, $charset = 'UTF-8'): string
    {
        return mb_strtolower(mb_substr($string, 0, 1, $charset), $charset) .
            mb_substr($string, 1, mb_strlen($string, $charset), $charset);
    }

    function getObjectResponsibleEmployees($request)
    {
        return Employee::where('object_id', $request->project_object_id)
            ->rightJoin('object_responsible_users', 'object_responsible_users.user_id', '=', 'employees.user_id')
            ->rightJoin('users', function ($join) {
                $join->on('employees.user_id', '=', 'users.id');
                $join->on('company_id', '=', 'users.company');
            })
            ->get('employees.*');
    }

    function getWorkersListForTemplate($request, $order)
    {
        $workersList = '<ol style="list-style-type: disc;">';

        $workers = LaborSafetyOrderWorker::where('labor_safety_order_workers.request_id', '=', $request->id)
            ->where('labor_safety_order_workers.order_type_id', '=', $order->order_type_id)
            ->leftJoin('labor_safety_request_workers', 'labor_safety_request_workers.id', '=', 'labor_safety_order_workers.requests_worker_id')
            ->get(
                [
                    'labor_safety_order_workers.*',
                    'labor_safety_request_workers.worker_type_id'
                ]
            );

        foreach ($workers as $worker) {
            switch ($order->order_type_id) {
                case 20:
                    if ($worker->worker_type_id == 6) {
                        continue 2;
                    }
            }

            $employeeId = LaborSafetyRequestWorker::find($worker->requests_worker_id)->worker_employee_id;
            $employee = Employee::find($employeeId);
            $postName = Employees1cPost::find($employee->employee_1c_post_id)->getInflection('винительный');

            $workersList .= '<li>' . $postName . ' — ' . $employee->format('L F P', 'винительный') . ';</li>';
        }

        $workersList .= '</ol>';
        return $workersList;
    }

    function getSignList($request, $order)
    {
        $signList = '<table style="width: 100%;"><tbody>';

        $workers = LaborSafetyOrderWorker::join('labor_safety_request_workers', 'requests_worker_id', '=', 'labor_safety_request_workers.id')
            ->where('labor_safety_order_workers.request_id', '=', $request->id)
            ->where('order_type_id', '=', $order->order_type_id)
            ->orderBy('worker_type_id')
            ->get(
                [
                    'worker_employee_id'
                ]
            );

        $isWorkerFirstInList = true;

        foreach ($workers as $worker) {
            $employeeName = Employee::find($worker->worker_employee_id)->format(' f. p. L');
            if ($isWorkerFirstInList) {
                $isWorkerFirstInList = false;
                $signList .= '<tr style="height: 76px;"><td style="width: 33%; height: 10px;"><p>С&nbsp;приказом&nbsp;ознакомлен:</p></td><td style="width: 33%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td><td style="height: 10px; width: 33%;"><p style="text-align: right;">' . $employeeName . '</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr>';
            } else {
                $signList .= '<tr style="height: 76px;"><td style="width: 33%; height: 10px;"></td><td style="width: 33%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td><td style="height: 10px; width: 33%;"><p style="text-align: right;">' . $employeeName . '</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr>';
            }
        }

        $signList .= '</tbody></table>';

        return $signList;
    }

    function getOrdersList($request)
    {
        $orders = LaborSafetyOrderWorker::where('request_id', '=', $request->id)
            ->leftJoin('labor_safety_order_types', 'labor_safety_order_workers.order_type_id', '=', 'labor_safety_order_types.id')
            ->distinct()
            ->get([
                'labor_safety_order_workers.order_type_id',
                'labor_safety_order_types.full_name',
                'labor_safety_order_types.short_name'
            ]);

        $ordersListHtml = '<ol>';
        foreach ($orders as $order) {
            $ordersListHtml .= '<li>Приказ №' . $request->order_number . '-' . $order->short_name . ' от ' . Carbon::parse($request->order_date)->format('d.m.Y') . ' г. «' . $order->full_name . '» — на 1 листе, 1 экз. — оригинал;</li>';
        }

        $ordersListHtml .= '</ol>';

        return $ordersListHtml;
    }

    function isEmployeeParticipatesInOrder($requestId, $employeeId, $orderTypeId)
    {
        return LaborSafetyOrderWorker::where('worker_employee_id', '=', $employeeId)
            ->where('labor_safety_order_workers.order_type_id', '=', $orderTypeId)
            ->where('labor_safety_order_workers.request_id', '=', $requestId)
            ->leftJoin('labor_safety_request_workers', 'requests_worker_id', '=', 'labor_safety_request_workers.id')
            ->exists();
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
                case "{company_name}":
                    $companyTemplate = str_replace($variable, $company->name, $companyTemplate);
                    break;
            }
        }

        return $companyTemplate;
    }

    private function sendRequestNotification($requestRow)
    {
        $notificationText = '';
        $users = [];

        switch ($requestRow->request_status_id) {
            case 1:
                $permissionId = Permission::where('codename', 'labor_safety_generate_documents_access')->first()->id;
                $users = UserPermission::where('permission_id', $permissionId)->get();
                $notificationText = "Поступила новая заявка на формирование приказов (#$requestRow->id).";
                break;
            case 3:
                $users = [$requestRow->author_id];
                $notificationText = "Заявка на формирование приказов #$requestRow->id отменена. Для уточнения информации обратитесь в отдел по Охране Труда.";
                break;
            case 4:
                $users = [$requestRow->author_id];
                $notificationText = "Документы по заявке на формирование приказов #$requestRow->id подписаны.";
                break;
        }

        foreach ($users as $userId) {
            $notification = new Notification();
            $notification->save();
            $notification->update([
                'name' => $notificationText,
                'target_id' => $requestRow->id,
                'user_id' => $userId,
                'created_at' => now(),
                'type' => 7,
                'status' => 7
            ]);
        }
    }

    public function getRequestWorkers(Request $request)
    {
        $requestId = json_decode($request['requestId']);

        $workers = LaborSafetyRequestWorker::where('request_id', '=', $requestId)
            ->leftJoin('employees', 'labor_safety_request_workers.worker_employee_id', 'employees.id')
            ->leftJoin('companies', 'employees.company_id', '=', 'companies.id')
            ->leftJoin('employees_1c_posts', 'employees.employee_1c_post_id', '=', 'employees_1c_posts.id')
            ->leftJoin('labor_safety_worker_types', 'labor_safety_request_workers.worker_type_id', '=', 'labor_safety_worker_types.id')
            ->where(function ($query) {
                if (!Auth::user()->can('labor_safety_generate_documents_access')) {
                    $query->where('worker_type_id', '=', 3);
                }
            })
            ->orderBy('labor_safety_request_workers.worker_type_id')
            ->get(
                [
                    'labor_safety_request_workers.id',
                    'labor_safety_request_workers.worker_employee_id',
                    'labor_safety_worker_types.id as employee_role_id',
                    'labor_safety_worker_types.name as employee_role',
                    'employees.id as employee_id',
                    'employee_1c_name',
                    'companies.name as company_name',
                    'employees_1c_posts.name as post_name'
                ]
            )
            ->toArray();

        foreach ($workers as $key => $value) {
            $workers[$key]['orders'] = LaborSafetyOrderWorker::where('request_id', '=', $requestId)
                ->where('requests_worker_id', '=', $value['id'])
                ->pluck('order_type_id');
        }

        return json_encode($workers);
    }

    public function getRequestWorkersTypes(Request $request)
    {
        $options = json_decode($request['data']);

        return (new LaborSafetyWorkerType())
            ->dxLoadOptions($options)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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

        $phpWord->setDefaultParagraphStyle(["spaceBefore" => 0, "spaceAfter" => 0]);
        $phpWord->setDefaultFontName('Calibri');

        $proofState = new ProofState();
        $proofState->setGrammar(ProofState::CLEAN);
        $proofState->setSpelling(ProofState::CLEAN);
        $phpWord->getSettings()->setDecimalSymbol(',');
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::RU_RU));
        $phpWord->getSettings()->setProofState($proofState);

        $section = $phpWord->addSection();

        LaborSafetyHtml::addHtml($section, $html, false, false);

        $phpWord->save('Список приказов.docx', 'Word2007', true);
        exit;
    }
}

<?php

namespace App\Http\Controllers\LaborSafety;

use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyRequest;
use App\Models\LaborSafety\LaborSafetyRequestOrder;
use App\Models\LaborSafety\LaborSafetyRequestStatus;
use App\Models\OneC\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class LaborSafetyRequestController extends Controller
{
    const PAGE_BREAK_DELIMITER = '';

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

        return (new LaborSafetyRequest())
            ->dxLoadOptions($loadOptions)
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

        $orders = $data["ordersData"];
        unset($data["ordersData"]);

        DB::beginTransaction();
        $laborSafetyRequestRow = new LaborSafetyRequest([
            'order_date' => Carbon::parse($data['order_date']),
            'company_id' => $data['company_id'],
            'project_object_id' => $data['project_object_id'],
            'author_user_id' => Auth::id(),
            'request_status_id' => 1,
        ]);

        $laborSafetyRequestRow->save();

        $this->insertOrUpdateOrdersData($orders, $laborSafetyRequestRow->id);

        DB::commit();
        return response()->json([
            'result' => 'ok',
            'key' => $laborSafetyRequestRow->id
        ], 200);
    }

    public function insertOrUpdateOrdersData($orders, $requestId)
    {
        foreach ($orders as $order) {

            $orderTypeId = $order[0];

            if (empty($orderTypeId)) {
                continue;
            }

            $order[1]['request_id'] = (int)$requestId;
            $order[1]['order_type_id'] = $orderTypeId;

            LaborSafetyRequestOrder::updateOrCreate(['request_id' => $requestId, 'order_type_id' => $orderTypeId], $order[1]);
        }
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
        }

        $requestRow->update($modifiedData);

        DB::commit();
        return response()->json([
            'result' => 'ok'
        ], 200);
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

    public function generateRequestHtmlData($request)
    {
        $orders = LaborSafetyRequestOrder::join('labor_safety_order_types', 'labor_safety_request_orders.order_type_id', '=', 'labor_safety_order_types.id')
            ->where('labor_safety_request_orders.request_id', '=', $request->id)
            ->get();

        $resultHtml = '';

        foreach ($orders as $order) {
            $orderTemplate = $this->fillTemplateData($request, $order, $order->template);

            $resultHtml .= $orderTemplate . self::PAGE_BREAK_DELIMITER;
        }

        return $resultHtml;
    }

    function fillTemplateData($request, $order, $orderTemplate){
        $variables = $this->getArrayOfTemplateVariables($orderTemplate);
        foreach($variables as $variable) {
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
                case "{responsible_employee_full_name}":
                    $employeeName = Employee::find($order->responsible_employee_id)->employee_1c_name;
                    $orderTemplate = str_replace($variable, $employeeName, $orderTemplate);
                    break;
            }
        }

        return $orderTemplate;
    }

    function getArrayOfTemplateVariables($orderTemplate) {
        $variables = [];

        preg_match_all('/\{(.)+?\}/', $orderTemplate, $variables);
        return array_unique($variables[0]);
    }

    function download(Request $request) {
        $requestId = json_decode($request->input('requestId'));
        $html = LaborSafetyRequest::findOrFail($requestId)->generated_html;
        $html = str_replace('<br>','<br/>', $html);
        $html = str_replace('<hr>','<hr/>', $html);

        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        Html::addHtml($section, $html, false, false);

        $phpWord->save('File.docx', 'Word2007', true);
        exit;
    }
}

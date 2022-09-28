<?php

namespace App\Http\Controllers\LaborSafety;

use App\Models\LaborSafety\LaborSafetyOrderType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LaborSafetyOrderTypeController extends Controller
{
    /**
     * Display a view of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response\Illuminate\View\View
     */
    public function index()
    {
        return view('labor-safety.labor-safety-order-types');
    }

    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = json_decode($request['loadOptions']);
        $requestId = json_decode($request['requestId']);

        $query = (new LaborSafetyOrderType())
            ->dxLoadOptions($loadOptions);

        if (!empty($requestId)) {
            $query->addSelect([
                'labor_safety_order_types.*',
                DB::Raw('(SELECT `order_type_id` from `labor_safety_request_orders` where `include_in_formation` = 1 and `request_id` = ' . $requestId . ' and  `order_type_id` = `labor_safety_order_types`.`id`) as selected_order_type')
            ]);
        }

        return $query->get()
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
        $id = $request->all()["key"];
        $modifiedData = json_decode($request->all()["modifiedData"], JSON_OBJECT_AS_ARRAY);

        $materialSupplyPlanningRow = LaborSafetyOrderType::findOrFail($id);

        $materialSupplyPlanningRow->update($modifiedData);

        return response()->json([
            'result' => 'ok'
        ], 200);
    }
}

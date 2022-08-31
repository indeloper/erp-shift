<?php

namespace App\Http\Controllers\LaborSafety;

use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LaborSafetyRequestController extends Controller
{
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

        $laborSafetyRequestRow = new LaborSafetyRequest([
                "order_date" => strtotime($data->order_date),
                "company_id" => $data->company_id,
                "project_object_id" => $data->project_object_id,
                "author_user_id" => $data->author_user_id,
                "request_status_id" => 1,
            ]
        );

        $laborSafetyRequestRow->save();

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

        $materialSupplyPlanningRow = LaborSafetyOrderType::findOrFail($id);

        $materialSupplyPlanningRow->update($modifiedData);

        return response()->json([
            'result' => 'ok'
        ], 200);
    }
}

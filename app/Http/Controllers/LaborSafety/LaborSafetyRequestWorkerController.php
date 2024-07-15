<?php

namespace App\Http\Controllers\LaborSafety;

use App\Http\Controllers\Controller;
use App\Models\LaborSafety\LaborSafetyRequestWorker;
use Illuminate\Http\Request;

class LaborSafetyRequestWorkerController extends Controller
{
    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = json_decode($request['loadOptions']);

        $query = (new LaborSafetyRequestWorker())
            ->dxLoadOptions($loadOptions);

        return $query
            ->get()
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}

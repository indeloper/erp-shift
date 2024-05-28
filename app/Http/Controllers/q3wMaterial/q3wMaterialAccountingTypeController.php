<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Http\Controllers\Controller;
use App\Models\q3wMaterial\q3wMaterialAccountingType;
use Illuminate\Http\Request;

class q3wMaterialAccountingTypeController extends Controller
{
    public function show(Request $request)
    {
        $options = json_decode($request->get('data', '{}'), false);

        return (new q3wMaterialAccountingType())
            ->dxLoadOptions($options)
            ->get()
            ->toJSON(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}

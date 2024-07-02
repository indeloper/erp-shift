<?php

namespace App\Http\Controllers;

use App\Models\Contractors\ContractorContact;

class ContactController extends Controller
{

    public function index()
    {
        return response()->json([
            'data' => ContractorContact::all(),
        ]);
    }

}

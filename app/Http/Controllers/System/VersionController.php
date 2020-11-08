<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Version;

class VersionController extends Controller
{
    public function index()
    {
        return view('versions.index');
    }


    public function store(Request $request)
    {
        return redirect()->route('versions.index');
    }
}

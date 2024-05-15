<?php

namespace App\Http\Controllers\System;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function index(): View
    {
        return view('versions.index');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('versions.index');
    }
}

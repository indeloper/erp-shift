<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

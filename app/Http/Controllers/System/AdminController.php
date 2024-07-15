<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function admin(): View
    {
        return view('support.admin');
    }

    public function sendTechUpdateNotify(Request $request)
    {
        $start_date_parsed = Carbon::parse($request->start_date)->isoFormat('D.MM.YYYY');
        $finish_date_parsed = Carbon::parse($request->finish_date)->isoFormat('D.MM.YYYY');

        Artisan::call("send:notify {$start_date_parsed} {$request->start_time} {$finish_date_parsed} {$request->finish_time}");

        return back();
    }

    public function loginAsUserId(Request $request): RedirectResponse
    {
        if (auth()->user()->is_su) {
            auth()->login(User::findOrFail($request->user_id), false);
        }

        return redirect('/');
    }
}

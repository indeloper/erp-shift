<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use phpDocumentor\Reflection\Types\Integer;
use App\Http\Requests\ReportGroupRequests\{ReportGroupDestroyRequest,
    ReportGroupStoreRequest,
    ReportGroupUpdateRequest};
use App\Services\AuthorizeService;
use App\Traits\AdditionalFunctions;
use App\Models\HumanResources\{JobCategory, PayAndHold, ReportGroup};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    use AdditionalFunctions;

    public function index(Request $request)
    {
        $this->authorize('human_resources_pay_and_hold_see');

        $payments = PayAndHold::payments()->get();
        $holds = PayAndHold::holds()->get();

        if (!$payments->count() and !$holds->count()) {
            $payments = PayAndHold::all();
        }
        return view('human_resources.payments.index', [
            'data' => [
                'payments' => $payments,
                'holds' => $holds,
            ],
        ]);
    }

    public function edit($id)
    {
        $this->authorize('human_resources_pay_and_hold_edit');

        return view('human_resources.payments.edit', [
            'data' => [
                'payment' => PayAndHold::findOrFail($id),
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('human_resources_pay_and_hold_edit');

        $pah = PayAndHold::findOrFail($id);
        DB::beginTransaction();

        $pah->short_name = $request->short_name;
        $pah->save();

        DB::commit();
        return ['redirect' => route('human_resources.payment.index')];
    }
}

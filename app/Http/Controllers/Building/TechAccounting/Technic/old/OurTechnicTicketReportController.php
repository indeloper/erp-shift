<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use App\Http\Controllers\Controller;
use App\Http\Requests\Building\TechAccounting\OurTechnic\OurTechnicTicketReportRequests\StoreOurTechnicTicketReportRequest;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Services\TechAccounting\TechnicTicketReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OurTechnicTicketReportController extends Controller
{
    private $report_service;

    public function __construct()
    {
        parent::__construct();

        $this->report_service = new TechnicTicketReportService();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOurTechnicTicketReportRequest $request, OurTechnicTicket $ourTechnicTicket)
    {
        $authed_id = Auth::user()->id;
        $has_report_on_date = $ourTechnicTicket->reports()->where('date', $request->date)->exists();
        if (!$ourTechnicTicket->users()->wherePivot('type', 4)->pluck('id')->contains($authed_id) or $has_report_on_date) return abort(403);

        $new_report = $ourTechnicTicket->reports()->create(array_merge($request->all(), ['user_id' => $authed_id]));

        $ourTechnicTicket->loadAllMissingRelations();

        $this->report_service->checkAndCloseTaskForUserIdForDate($authed_id, Carbon::parse($request->date));

        return response()->json([
            'result' => 'success',
            'data' => $ourTechnicTicket
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TechAcc\OurTechnicTicketReport  $ourTechnicTicketReport
     * @return \Illuminate\Http\Response
     */
    public function update(StoreOurTechnicTicketReportRequest $request, OurTechnicTicket $ourTechnicTicket, OurTechnicTicketReport $ourTechnicTicketReport)
    {
        $authed_id = Auth::user()->id;
        $has_report_on_date = $ourTechnicTicket->reports()->where('date', $request->date)->where('id', '!=', $ourTechnicTicketReport->id)->exists();
        if ($ourTechnicTicketReport->user_id != $authed_id) return abort(403);

        $ourTechnicTicketReport->update($request->all());

        $ourTechnicTicket->loadAllMissingRelations();

        return response()->json([
            'result' => 'success',
            'data' => $ourTechnicTicket
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\TechAcc\OurTechnicTicketReport $ourTechnicTicketReport
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(OurTechnicTicket $ourTechnicTicket, OurTechnicTicketReport $ourTechnicTicketReport)
    {
        $authed_id = Auth::user()->id;

        if ($ourTechnicTicketReport->user_id != $authed_id) return abort(403);

        $ourTechnicTicketReport->delete();
        $ourTechnicTicket->loadAllMissingRelations();

        return response()->json([
            'result' => 'success',
            'data' => $ourTechnicTicket
        ]);
    }
}

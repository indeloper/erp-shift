<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use App\Http\Requests\DynamicTicketUpdateRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Models\Notification;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use App\Services\TechAccounting\TechnicTicketService;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class OurTechnicTicketController extends Controller
{
    /**
     * @var TechnicTicketService
     */
    protected $our_ticket_service;

    public function __construct()
    {
        parent::__construct();

        $this->our_ticket_service = new TechnicTicketService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $data = $this->our_ticket_service->collectShortTicketsData($request->all());

        return view('tech_accounting.tickets.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TicketStoreRequest $request
     * @return Response
     */
    public function store(TicketStoreRequest $request)
    {
        $new_ticket = $this->our_ticket_service->createNewTicket($request->all());

        return response()->json($new_ticket);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DynamicTicketUpdateRequest $request
     * @param OurTechnicTicket $ourTechnicTicket
     * @return Response
     */
    public function update(DynamicTicketUpdateRequest $request, OurTechnicTicket $ourTechnicTicket)
    {
        $updated_ticket = $this->our_ticket_service->updateTicketStatus($ourTechnicTicket, $request->all());

        return response([
            'data' => $updated_ticket,
            'status' => 'success',
        ]);
    }

    /**
     * @param OurTechnicTicket $ourTechnicTicket
     * @return Response
     * @throws \Exception
     */
    public function destroy(OurTechnicTicket $ourTechnicTicket)
    {
        $ourTechnicTicket->close()->delete();

        return Response::create([
            'result' => 'success',
        ]);
    }


    public function show(OurTechnicTicket $ourTechnicTicket)
    {
        return \response([
            'data' => [
                'ticket' => $ourTechnicTicket->loadAllMissingRelations()
            ],
        ]);
    }

    public function getTechnicTickets(Request $request): array
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);

        $paginated = OurTechnicTicket::filter($output)->permissionCheck()->paginate(15);

        $ourTechnicTicketsCount = $paginated->total();
        $ourTechnicTickets = $paginated->items();

        return [
            'ourTechnicTicketsCount' => $ourTechnicTicketsCount,
            'ourTechnicTickets' => $ourTechnicTickets,
        ];
    }

    public function reassignment(OurTechnicTicket $ourTechnicTicket, Request $request)
    {
        DB::beginTransaction();

        if (in_array($request->task_status, [31, 32])) {
            $ourTechnicTicket->tasks()->create([
                'name' => 'Подтверждение перемещения',
                'responsible_user_id' => $request->user,
                'expired_at' => $this->addHours(24),
                'status' => $request->task_status
            ]);

            $user = User::findOrFail($request->user);
            $ourTechnicTicket->comments()->create([
                'comment' => "Передано право на подтверждение перемещения пользователю {$user->long_full_name}",
                'author_id' => auth()->id(),
                'system' => 1,
            ]);
            // here we have many responsible user (every one can send and receive tech)
            $ourTechnicTicket->users()->attach($request->user, ['type' => $request->task_status == 31 ? 2 : 3]);

            $notification = new Notification();
            $notification->save();
            $notification->additional_info = "\n" .
                "Ссылка: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notification->update([
                'name' => "Необходимо обработать заявку на {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model}",
                'user_id' => $request->user,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => $request->task_status == 31 ? 71 : 72,
            ]);
        }
        elseif ($request->task_status == 36) {
            $user = User::findOrFail($request->user);
            $ourTechnicTicket->comments()->create([
                'comment' => "Передано право на использование {$user->long_full_name}",
                'author_id' => auth()->id(),
                'system' => 1,
            ]);
            //here only one person at a time can send usage reports. old user for his own time period, new one for the rest of time
            $ourTechnicTicket->users()->ofType('usage_resp_user_id')->where('deactivated_at', null)->update(['deactivated_at' => Carbon::now()->isoFormat('YYYY-MM-DD')]);

            if ($ourTechnicTicket->users()->ofType('usage_resp_user_id')->where('user_id', $user->id)->exists()) {
                $ourTechnicTicket->users()->ofType('usage_resp_user_id')->where('user_id', $user->id)->update(['deactivated_at' => null]);
            } else {
                $ourTechnicTicket->users()->attach($request->user, ['type' => 4]);
            }


            $notification = new Notification();
            $notification->save();
            $notification->additional_info = "\n" .
                "Ссылка: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notification->update([
                'name' => "Вас назначили ответсвенным за использование техники {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model}",
                'user_id' => $request->user,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => 69,
            ]);
        }
        DB::commit();

        return response()->json(true);
    }
}

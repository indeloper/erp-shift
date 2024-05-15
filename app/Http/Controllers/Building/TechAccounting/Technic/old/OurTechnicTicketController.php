<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicTicketUpdateRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use App\Notifications\Technic\TechnicDispatchConfirmationNotice;
use App\Notifications\Technic\TechnicReceiptConfirmationNotice;
use App\Notifications\Technic\TechnicUsageStartTaskNotice;
use App\Services\TechAccounting\TechnicTicketService;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OurTechnicTicketController extends Controller
{
    use TimeCalculator;

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
     */
    public function index(Request $request): View
    {
        $data = $this->our_ticket_service->collectShortTicketsData($request->all());

        return view('tech_accounting.tickets.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketStoreRequest $request): JsonResponse
    {
        $new_ticket = $this->our_ticket_service->createNewTicket($request->all());

        return response()->json($new_ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DynamicTicketUpdateRequest $request, OurTechnicTicket $ourTechnicTicket): Response
    {
        $updated_ticket = $this->our_ticket_service->updateTicketStatus($ourTechnicTicket, $request->all());

        return response([
            'data' => $updated_ticket,
            'status' => 'success',
        ]);
    }

    /**
     *
     * @throws \Exception
     */
    public function destroy(OurTechnicTicket $ourTechnicTicket): Response
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
                'ticket' => $ourTechnicTicket->loadAllMissingRelations(),
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

    public function reassignment(OurTechnicTicket $ourTechnicTicket, Request $request): JsonResponse
    {
        DB::beginTransaction();

        if (in_array($request->task_status, [31, 32])) {
            $ourTechnicTicket->tasks()->create([
                'name' => 'Подтверждение перемещения',
                'responsible_user_id' => $request->user,
                'expired_at' => $this->addHours(24),
                'status' => $request->task_status,
            ]);

            $user = User::findOrFail($request->user);
            $ourTechnicTicket->comments()->create([
                'comment' => "Передано право на подтверждение перемещения пользователю {$user->long_full_name}",
                'author_id' => auth()->id(),
                'system' => 1,
            ]);
            // here we have many responsible user (every one can send and receive tech)
            $ourTechnicTicket->users()->attach($request->user, ['type' => $request->task_status == 31 ? 2 : 3]);

            /** Отправка в уведомлений */
            $noticeClass = $request->task_status == 31 ? TechnicDispatchConfirmationNotice::class : TechnicReceiptConfirmationNotice::class;
            $noticeClass::send(
                $request->user,
                [
                    'name' => "Необходимо обработать заявку на {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model}",
                    'additional_info' => 'Ссылка: ',
                    'url' => route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                    'created_at' => now(),
                    'target_id' => $ourTechnicTicket->id,
                ]
            );
        } elseif ($request->task_status == 36) {
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

            /** Отправка в уведомлений */
            TechnicUsageStartTaskNotice::send(
                $request->user,
                [
                    'name' => "Вас назначили ответственным за использование техники {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model}",
                    'additional_info' => "\nСсылка: ",
                    'url' => route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                    'created_at' => now(),
                    'target_id' => $ourTechnicTicket->id,
                ]
            );
        }
        DB::commit();

        return response()->json(true);
    }
}

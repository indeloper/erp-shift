<?php

namespace App\Jobs;

use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use App\Services\TechAccounting\TechnicTicketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoConfirmTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var OurTechnicTicket
     */
    private $ticket;

    /**
     * @var TechnicTicketService
     */
    private $ticket_service;

    /**
     * Create a new job instance.
     */
    public function __construct(OurTechnicTicket $ticket)
    {
        $this->ticket = $ticket;
        $this->ticket_service = new TechnicTicketService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->ticket->status == 1) {
            $request = [
                'acceptance' => 'confirm',
                //                'process_resp_user_id' => (new User())->main_logist_id,
            ];
            $this->ticket_service->updateTicketStatus($this->ticket, $request, 1);
        }
    }
}

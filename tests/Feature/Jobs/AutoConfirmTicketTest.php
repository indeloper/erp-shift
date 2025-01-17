<?php

namespace Tests\Feature\Jobs;

use App\Jobs\AutoConfirmTicket;
use App\Models\TechAcc\OurTechnicTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoConfirmTicketTest extends TestCase
{
    /** @test */
    public function it_changes_ticket_status_after_delay(): void
    {
        $ticket = OurTechnicTicket::factory()->create();

        AutoConfirmTicket::dispatchSync($ticket);

        $ticket->refresh();
        $this->assertEquals($ticket->status, 2);
    }

    /** @test */
    public function it_dispatches_job_after_delay(): void
    {
        $ticket = OurTechnicTicket::factory()->create();

        $old_ticket = OurTechnicTicket::factory()->create(['created_at' => Carbon::now()->subDay()]);

        Artisan::call('ticket:auto_confirm');
        $old_ticket->refresh();
        $ticket->refresh();

        $this->assertEquals($old_ticket->status, 2);
        $this->assertEquals($ticket->status, 1);
    }
}

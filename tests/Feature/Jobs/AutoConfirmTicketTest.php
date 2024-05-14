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
    public function it_changes_ticket_status_after_delay()
    {
        $ticket = factory(OurTechnicTicket::class)->create();

        AutoConfirmTicket::dispatchNow($ticket);

        $ticket->refresh();
        $this->assertEquals($ticket->status, 2);
    }

    /** @test */
    public function it_dispatches_job_after_delay()
    {
        $ticket = factory(OurTechnicTicket::class)->create();

        $old_ticket = factory(OurTechnicTicket::class)->create(['created_at' => Carbon::now()->subDay()]);

        Artisan::call('ticket:auto_confirm');
        $old_ticket->refresh();
        $ticket->refresh();

        $this->assertEquals($old_ticket->status, 2);
        $this->assertEquals($ticket->status, 1);
    }
}

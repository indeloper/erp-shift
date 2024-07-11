<?php

namespace Tests\Feature\Tech_accounting\OurTechnicTicket;

use App\Models\TechAcc\OurTechnicTicket;
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Services\TechAccounting\TechnicTicketReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class OurTechnicTicketReportTest extends OurTechnicTicketTestCase
{
    protected $ourTechnicTicket;

    protected $valide_fields;

    protected $response_user;

    /**
     * A setUp for next tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        $migrations = [];

        $this->response_user = $this->rps->random();
        $this->actingAs($this->response_user);
        $this->ourTechnicTicket = $this->seedTicketsWithUsers(1, [],
            ['usage_resp_user_id' => Auth::user()->id])->first();
        $this->response_user = $this->ourTechnicTicket->users()->wherePivot('type', 4)->first();
    }

    /** @test */
    public function it_can_store_ticket_report() //store
    {
        $this->post(route('building::tech_acc::our_technic_tickets.report.store', $this->ourTechnicTicket->id),
            OurTechnicTicketReport::factory()
                ->make([
                    'our_technic_ticket_id' => $this->ourTechnicTicket->id,
                ])
                ->toArray()
        );
        // ->assertSee('success');
        $this->assertEquals($this->ourTechnicTicket->id,
            OurTechnicTicketReport::latest()->first()->our_technic_ticket_id);
        $this->assertEquals($this->response_user->id,
            OurTechnicTicketReport::latest()->first()->ticket->users()->wherePivot('type', 4)->first()->id);
    }

    /** @test */
    public function it_can_update_ticket_report() //update
    {
        $old_ticket = OurTechnicTicketReport::factory()
            ->create([
                'our_technic_ticket_id' => $this->ourTechnicTicket->id,
            ]);

        $this->put(route('building::tech_acc::our_technic_tickets.report.update',
            [$old_ticket->our_technic_ticket_id, $old_ticket->id]),
            [
                'comment' => $this->faker()->paragraph,
                'hours' => $old_ticket->hours != 1 ? 1 : 2,
            ]
        );
        // ->assertSee('success');

        $this->assertEquals($old_ticket->id, OurTechnicTicketReport::latest()->first()->id);
        $this->assertNotEquals($old_ticket->comment, OurTechnicTicketReport::latest()->first()->comment);
        $this->assertNotEquals($old_ticket->hours, OurTechnicTicketReport::latest()->first()->hours);
    }

    /** @test */
    public function it_can_destroy_ticket_report() //delete
    {
        $ticket_need_delete = OurTechnicTicketReport::factory()->count(2)
            ->create([
                'our_technic_ticket_id' => $this->ourTechnicTicket->id,
                'user_id' => $this->response_user,
            ]);

        $this->delete(route('building::tech_acc::our_technic_tickets.report.destroy',
            [$ticket_need_delete->first()->our_technic_ticket_id, $ticket_need_delete->first()->id]));
        $ticket_need_delete->fresh();

        $this->assertSoftDeleted($ticket_need_delete->first());
        $this->assertEquals(1, OurTechnicTicketReport::count());
    }

    /** @test */
    public function it_doesnt_close_task_when_last_report_for_today_has_been_closed(): void
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 7, 'usage_from_date' => Carbon::now()],
            ['usage_resp_user_id' => $this->response_user->id])->first();

        $task = $this->response_user->tasks()->create([
            'name' => 'Отметка времени использования техники за '.Carbon::now()->isoFormat('DD.MM.YYYY'),
            'expired_at' => $this->addHours(8),
            'status' => 36,
        ]);

        $this->post(route('building::tech_acc::our_technic_tickets.report.store', $ticket->id),
            OurTechnicTicketReport::factory()
                ->raw([
                    'our_technic_ticket_id' => $ticket->id,
                ])
        );

        $task->refresh();

        $this->assertFalse(boolval($task->is_solved), 'Task is_solved should be 0, but it is: '.$task->is_solved);
    }

    /** @test */
    public function it_do_not_close_task_when_there_are_still_reports_to_make(): void
    {
        $ticket = $this->seedTicketsWithUsers(2, ['status' => 7, 'usage_from_date' => Carbon::now()],
            ['usage_resp_user_id' => $this->response_user->id])->first();

        $task = $this->response_user->tasks()->create([
            'name' => 'Отметка времени использования техники за '.Carbon::now()->isoFormat('DD.MM.YYYY'),
            'expired_at' => $this->addHours(8),
            'status' => 36,
        ]);

        $this->post(route('building::tech_acc::our_technic_tickets.report.store', $ticket->id),
            OurTechnicTicketReport::factory()
                ->raw([
                    'our_technic_ticket_id' => $ticket->id,
                ])
        );

        $task->refresh();

        $this->assertFalse(boolval($task->is_solved), 'Task is_solved should be 0, but it is: '.$task->is_solved);
    }

    /** @test */
    public function it_closes_task_on_last_report_in_the_past(): void
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 7, 'usage_from_date' => Carbon::now()->subDays(4)],
            ['usage_resp_user_id' => $this->response_user->id])->first();
        $date = Carbon::now()->subDays(4);

        $task = $this->response_user->tasks()->create([
            'name' => 'Отметка времени использования техники за '.$date->isoFormat('DD.MM.YYYY'),
            'expired_at' => $this->addHours(8),
            'status' => 36,
        ]);
        $task->created_at = $date;
        $task->save();

        $this->post(route('building::tech_acc::our_technic_tickets.report.store', $ticket->id),
            OurTechnicTicketReport::factory()
                ->raw([
                    'our_technic_ticket_id' => $ticket->id,
                    'date' => $date->isoFormat('YYYY-MM-DD'),
                ])
        );

        $task->refresh();

        $this->assertTrue(boolval($task->is_solved), 'Task is_solved should be 1, but it is: '.$task->is_solved);
    }

    /** @test */
    public function it_groups_tickets_by_usage_user(): void
    {
        $this->seedTicketsWithUsers(3, ['status' => 7], ['usage_resp_user_id' => '']);
        $this->seedTicketsWithUsers(3, ['status' => 7], ['usage_resp_user_id' => $this->rps_and_prorabs[1]->id]);

        $grouped_ticket = OurTechnicTicket::where('status', 7)->get()->groupBy(function ($item) {
            return $item->users()->ofType('usage_resp_user_id')->first()->id ?? '-1';
        });

        $this->assertCount(3, $grouped_ticket->first());
    }

    /** @test */
    public function new_usage_resp_get_report_task_not_old_one(): void
    {
        $Mark = $this->prorabs->random(); //actually he is not Mark
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 7, 'usage_from_date' => Carbon::now()],
            ['usage_resp_user_id' => $this->response_user->id])->first();

        //test works too fast, so we need to change timestamp manually
        $old_resp_pivot = $ticket->users()->where('type', 4)->where('id', $this->response_user->id)->first();
        $old_resp_pivot->created_at = Carbon::now()->subDay();
        $old_resp_pivot->save();

        $this->post(route('building::tech_acc::our_technic_tickets.reassignment', $ticket->id), [
            'result' => 'usage',
            'user' => $Mark->id,
            'task_status' => 36,
        ])->assertOk();

        Artisan::call('usage_report_task:create');

        $this->assertCount(1, $Mark->tasks);
        $this->assertEmpty($this->response_user->tasks);
        $this->assertEquals($this->response_user->id, $ticket->users()->where('deactivated_at', '!=', '')->first()->id);
    }

    /** @test */
    public function it_can_properly_set_old_resp_as_active(): void
    {
        $Mark = $this->prorabs->random(); //actually he is not Mark
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 7, 'usage_from_date' => Carbon::now()], [
            'usage_resp_user_id' => $this->response_user->id, //one resp
            'recipient_user_id' => $this->response_user->id, //and another one
        ])->first();

        $old_resp_count = $ticket->users()->where('user_id', $this->response_user->id)->count();
        //test works too fast, so we need to change timestamp manually
        $old_resp_pivot = $ticket->users()->where('type', 4)->where('id', $this->response_user->id)->first();
        $old_resp_pivot->created_at = Carbon::now()->subDay();
        $old_resp_pivot->save();

        $this->post(route('building::tech_acc::our_technic_tickets.reassignment', $ticket->id), [
            'result' => 'usage',
            'user' => $Mark->id,
            'task_status' => 36,
        ])->assertOk();

        //trying to return styles resp back
        $this->post(route('building::tech_acc::our_technic_tickets.reassignment', $ticket->id), [
            'result' => 'usage',
            'user' => $this->response_user->id,
            'task_status' => 36,
        ])->assertOk();

        //there must be the same amount of resps with id of styles resp
        $this->assertEquals($old_resp_count, $ticket->users()->where('user_id', $this->response_user->id)->count());
        //and Mark should be deactivated
        $this->assertEquals($Mark->id, $ticket->users()->where('deactivated_at', '!=', '')->first()->id);
    }

    /** @test */
    public function it_creates_new_task_and_closes_old_automatically(): void
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 7, 'usage_from_date' => Carbon::now()->subDays(1)],
            ['usage_resp_user_id' => $this->response_user->id])->first();
        $date = Carbon::now()->subDays(1);

        $task = $this->response_user->tasks()->create([
            'name' => 'Отметка времени использования техники за '.$date->isoFormat('DD.MM.YYYY'),
            'expired_at' => $this->addHours(8),
            'status' => 36,
        ]);
        $task->created_at = $date;
        $task->save();

        OurTechnicTicketReport::factory()
            ->create([
                'our_technic_ticket_id' => $ticket->id,
                'date' => $date->isoFormat('YYYY-MM-DD'),
            ]);

        Artisan::call('usage_report_task:create');
        $task->refresh();
        $this->assertTrue(boolval($task->is_solved), 'Task is_solved should be 1, but it is: '.$task->is_solved);
        $this->assertEquals(2, $this->response_user->allTasks()->count());
    }

    /** @test */
    public function it_creates_new_task_for_old_dates(): void
    {
        //set usage_resp created_at to sub 3 days in seeder
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 7, 'usage_from_date' => Carbon::now()->subDays(10)],
            ['usage_resp_user_id' => $this->response_user->id])->first();
        $user = $ticket->users()->ofType('usage_resp_user_id')->first();

        $ser = new TechnicTicketReportService();
        $ser->createCloseTasksForEveryoneEveryday();

        $this->assertEquals(4, $user->tasks->count());
    }

    /** @test */
    public function it_creates_new_task_for_compicated_cases(): void
    {

        $ticket = $this->seedTicketsWithUsers(1, ['status' => 7, 'usage_from_date' => Carbon::now()->subDays(10)],
            ['usage_resp_user_id' => $this->response_user->id])->first();
        $user = $ticket->users()->ofType('usage_resp_user_id')->first();
        $userTwo = $this->rps_and_prorabs->random();
        $ticket->users()->attach($userTwo->id, ['type' => 4, 'created_at' => Carbon::now()->subDays(1)]);

        $ser = new TechnicTicketReportService();
        $ser->createCloseTasksForEveryoneEveryday();

        $this->assertEquals(2, $user->tasks->count());
        $this->assertEquals(2, $userTwo->tasks->count());
    }
}

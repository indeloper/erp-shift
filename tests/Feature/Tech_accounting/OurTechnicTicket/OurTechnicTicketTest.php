<?php

namespace Tests\Feature;

use App\Events\NotificationCreated;
use App\Http\Controllers\Building\TechAccounting\OurTechnicTicketActionsController;
use App\Http\Controllers\Building\TechAccounting\OurTechnicTicketController;
use App\Http\Requests\DynamicTicketUpdateRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\User;
use App\Services\TechAccounting\TechnicTicketService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Tech_accounting\OurTechnicTicket\OurTechnicTicketTestCase;

class OurTechnicTicketTest extends OurTechnicTicketTestCase
{
    /** @test */
    public function it_can_create_ticket()
    {
        $request = $this->validFields(['ticket_resp_user_id' => 1]);
        $this->service->createNewTicket($request);

        $this->assertCount(1, OurTechnicTicket::all());
        $this->assertEquals(1, OurTechnicTicket::first()->users()->wherePivot('type', 2)->first()->id);
    }

    /** @test */
    public function new_tickets_go_first()
    {
        $old_tickets = factory(OurTechnicTicket::class, 2)->create(['updated_at' => Carbon::now()->subDays(2)]);
        $newer_tickets = factory(OurTechnicTicket::class, 2)->create(['updated_at' => Carbon::now()->subDays(1)]);
        $brand_new_tickets = factory(OurTechnicTicket::class, 1)->create(['updated_at' => Carbon::now()]);

        $all_tickets = OurTechnicTicket::all();

        $this->assertEquals($brand_new_tickets->first()->id, $all_tickets->first()->id);
    }

    /** @test */
    public function it_creates_task_to_accept_ticket()
    {
        $this->actingAs(User::find(1)); //rp will skip this step and will go directly to usage(transfer)
        $ticket_service = new TechnicTicketService();

        $request = $this->validFields();
        $ticket = $ticket_service->createNewTicket($request);

        $this->assertNotEmpty($ticket->tasks);
        $this->assertEquals(28, $ticket->tasks()->first()->status);
        $this->assertEquals($request['resp_rp_user_id'], $ticket->tasks()->first()->responsible_user_id);
    }

    /** @test */
    public function it_can_return_users_by_scope()
    {
        $ticket = $this->seedTicketsWithUsers(1, [], ['author_user_id' => 6])->first();

        $author = $ticket->users()->ofType('author_user_id')->first();

        $this->assertEquals(6, $author->id);
    }

    /** @test */
    public function it_show_buttons_only_for_current_responsible()
    {
        $ticket = $this->seedTicketsWithUsers()->first();

        $this->actingAs($ticket->users()->ofType('resp_rp_user_id')->first());
        $this->assertTrue($ticket->show_buttons);

        $this->actingAs(User::take(2)->get()->last());
        $this->assertFalse($ticket->show_buttons);
    }

    /** @test */
    public function it_can_have_vehicles()
    {
        $ticket = factory(OurTechnicTicket::class)->create();
        $vehicle_collection = factory(OurVehicles::class,2 )->create();
        $vehicle_model = factory(OurVehicles::class)->create();

        $ticket->vehicles()->attach($vehicle_collection);
        $ticket->vehicles()->attach($vehicle_model);

        $this->assertCount(3, $ticket->vehicles()->get());
    }

    /** @test */
    public function it_can_go_through_path_step_by_step()
    {
        $controller = new OurTechnicTicketController();
        //2 => 'Ожидает назначения'
        $request = new TicketStoreRequest($this->validFields([
            'sending_from_date' => '',
            'sending_to_date' => '',
            'getting_from_date' => '',
            'ticket_resp_user_id' => '',
        ]));
        $controller->store($request);
        $ticket = OurTechnicTicket::first();

        $this->assertNotNull(1, $ticket);
        $this->assertEquals(2, $ticket->status);

        //2 => 'Ожидает назначения'
        $this->actingAs($this->logist);
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'sending_from_date' => Carbon::now(),
            'sending_to_date' => Carbon::now()->addDays(2),
            'getting_from_date' => Carbon::now()->addDays(2),
            'getting_to_date' => Carbon::now()->addDays(5),
            'ticket_resp_user_id' => $this->rps_and_prorabs->random()->id,
            'vehicle_ids' => [OurVehicles::first()->id],
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(6, $ticket->status);

        //6 => 'Перемещение'
        $this->actingAs($ticket->users()->ofType('request_resp_user_id')->first());
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'comment' => $this->faker()->sentence,
            'task_status' => 31,
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(6, $ticket->status);

        $this->actingAs($ticket->users()->ofType('recipient_user_id')->first());
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'comment' => $this->faker()->sentence,
            'task_status' => 32,
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(5, $ticket->status);

        //5 => 'Ожидает начала использования'
        $this->actingAs($ticket->users()->ofType('usage_resp_user_id')->first());
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'comment' => $this->faker()->sentence,
        ]);

        $controller->update($request, $ticket);
        $this->assertEquals(7, $ticket->status);

        //7 => 'Использование'
        (new OurTechnicTicketActionsController())->close($request, $ticket);
        //8 => 'Завершена'
        $this->assertEquals(8, $ticket->status);
    }

    /** @test */
    public function it_can_go_through_very_long_path_step_by_step()
    {
        $controller = new OurTechnicTicketController();
        //2 => 'Ожидает назначения'
        $request = new TicketStoreRequest($this->validFields([
            'sending_from_date' => '',
            'sending_to_date' => '',
            'getting_from_date' => '',
            'ticket_resp_user_id' => '',
        ]));
        $controller->store($request);
        $ticket = OurTechnicTicket::first();

        $this->assertNotNull(1, $ticket);
        $this->assertEquals(2, $ticket->status);

        //2 => 'Ожидает назначения'
        $this->actingAs($this->logist);
        $request = new DynamicTicketUpdateRequest([
            'result' => 'hold',
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(4, $ticket->status);

        //4 => 'Удержание'
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'sending_from_date' => Carbon::now(),
            'sending_to_date' => Carbon::now()->addDays(2),
            'getting_from_date' => Carbon::now()->addDays(2),
            'getting_to_date' => Carbon::now()->addDays(5),
            'ticket_resp_user_id' => $this->rps_and_prorabs->random()->id,
            'vehicle_ids' => [OurVehicles::first()->id],
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(6, $ticket->status);

        //6 => 'Перемещение'
        $this->actingAs($ticket->users()->ofType('request_resp_user_id')->first());
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'comment' => $this->faker()->sentence,
            'task_status' => 31,
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(6, $ticket->status);

        $this->actingAs($ticket->users()->ofType('recipient_user_id')->first());
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'comment' => $this->faker()->sentence,
            'task_status' => 32,
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(5, $ticket->status);

        //5 => 'Ожидает начала использования'
        $this->actingAs($ticket->users()->ofType('usage_resp_user_id')->first());
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'comment' => $this->faker()->sentence,
        ]);

        $controller->update($request, $ticket);
        $this->assertEquals(7, $ticket->status);

        //7 => 'Использование'
        (new OurTechnicTicketActionsController())->close($request, $ticket);
        //8 => 'Завершена'
        $this->assertEquals(8, $ticket->status);
    }

    /** @test */
    public function it_can_go_through_logist_failure_path()
    {
        $controller = new OurTechnicTicketController();
        //0 => 'Создание заявки'
        $request = new TicketStoreRequest($this->validFields());
        $controller->store($request);
        $ticket = OurTechnicTicket::first();

        $this->assertNotNull(1, $ticket);
        $this->assertEquals(2, $ticket->status);

        //2 => 'Ожидает назначения'
        $this->actingAs($this->logist);
        $request = new DynamicTicketUpdateRequest([
            'result' => 'hold',
            'vehicle_ids' => [OurVehicles::first()->id],
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(4, $ticket->status);


        //6 => 'Удержание'
        $request = new DynamicTicketUpdateRequest([
            'result' => 'reject',
            'comment' => $this->faker()->sentence,
        ]);

        $controller->update($request, $ticket);

        $this->assertEquals(3, $ticket->status);
    }

    /** @test */
    public function it_can_go_through_rp_failure_path()
    {
        $controller = new OurTechnicTicketController();
        $this->actingAs($this->prorabs->first());
        //0 => 'Создание заявки'
        $request = new TicketStoreRequest($this->validFields());
        $controller->store($request);
        $ticket = OurTechnicTicket::first();

        $this->assertNotNull(1, $ticket);
        $this->assertEquals(1, $ticket->status);

        //1 => 'Согласование заявки'
        $request = new DynamicTicketUpdateRequest([
            'acceptance' => 'reject',
        ]);
        $controller->update($request, $ticket);

        $this->assertEquals(3, $ticket->status);
    }

    /** @test */
    public function it_fires_seven_notification_after_logist_accepts_ticket()
    {
        $controller = new OurTechnicTicketController();

        $ticket = $this->seedTicketsWithUsers()->first();
        $ticket->status = 2;

        $this->actingAs($this->logist);
        $request = new DynamicTicketUpdateRequest([
            'result' => 'confirm',
            'vehicle_ids' => [factory(OurVehicles::class)->create()->id],
        ]);

        Event::fake([
            NotificationCreated::class,
        ]);
        $controller->update($request, $ticket);

        $expected_count = $ticket->users->unique()->count() + 2;
        Event::assertDispatched(NotificationCreated::class, $expected_count);

//        dd(collect(Event::dispatched(NotificationCreated::class))->flatten()->pluck('text')); //if you want to see text
        $this->assertEquals(6, $ticket->status);
    }

    /** @test */
    public function it_fires_five_notification_after_logist_holds_ticket()
    {
        $controller = new OurTechnicTicketController();

        $ticket = $this->seedTicketsWithUsers()->first();
        $ticket->status = 2;

        $this->actingAs($this->logist);
        $request = new DynamicTicketUpdateRequest([
            'result' => 'reject', //or hold
            'vehicle_ids' => [factory(OurVehicles::class)->create()->id],
        ]);

        Event::fake([
            NotificationCreated::class,
        ]);
        $controller->update($request, $ticket);

        $expected_count = $ticket->users->unique()->count();
        Event::assertDispatched(NotificationCreated::class, $expected_count);

//        dd(collect(Event::dispatched(NotificationCreated::class))->flatten()->pluck('text')); //if you want to see text
    }

    /** @test */
    public function ticket_reports_relation_return_reports_ordered_by_date_in_desc_order()
    {
        // Given ticket and reports
        $ticket = factory(OurTechnicTicket::class)->create();
        $report1 = $ticket->reports()->save(factory(OurTechnicTicketReport::class)->create(['our_technic_ticket_id' => $ticket->id, 'date' => now()->format('d.m.Y')]));
        $report2 = $ticket->reports()->save(factory(OurTechnicTicketReport::class)->create(['our_technic_ticket_id' => $ticket->id, 'date' => now()->subDay()->format('d.m.Y')]));
        $report3 = $ticket->reports()->save(factory(OurTechnicTicketReport::class)->create(['our_technic_ticket_id' => $ticket->id, 'date' => now()->subDays(2)->format('d.m.Y')]));

        // When we user reports() relation
        $reports = $ticket->reports;

        // Then reports should be ordered by date column
        $this->assertEquals([$report1->id, $report2->id, $report3->id], $reports->pluck('id')->toArray());
    }

    /** @test */
    public function ticket_reports_relation_return_reports_ordered_by_date_in_desc_order_one_more_time()
    {
        // Given ticket and reports
        $ticket = factory(OurTechnicTicket::class)->create();
        $report1 = $ticket->reports()->save(factory(OurTechnicTicketReport::class)->create(['our_technic_ticket_id' => $ticket->id, 'date' => now()->format('d.m.Y')]));
        $report2 = $ticket->reports()->save(factory(OurTechnicTicketReport::class)->create(['our_technic_ticket_id' => $ticket->id, 'date' => now()->subDay()->format('d.m.Y')]));
        $report3 = $ticket->reports()->save(factory(OurTechnicTicketReport::class)->create(['our_technic_ticket_id' => $ticket->id, 'date' => now()->subMonth()->day(28)->format('d.m.Y')]));

        // When we user reports() relation
        $reports = $ticket->reports;

        // Then reports should be ordered by date column
        $this->assertEquals([$report1->id, $report2->id, $report3->id], $reports->pluck('id')->toArray());
    }

    /** @test */
    public function users_who_have_permissions_can_see_all_tickets()
    {
        // Given tickets
        $ticket1 = factory(OurTechnicTicket::class)->create();
        $ticket2 = factory(OurTechnicTicket::class)->create();
        $ticket3 = factory(OurTechnicTicket::class)->create();

        // When we use OurTechnicTicket::filter()
        // as principle
        $principle = User::whereGroupId(47)->where('is_deleted', 0)->first() ?? factory(User::class)->create(['group_id' => 47]);
        $this->actingAs($principle);
        $results = OurTechnicTicket::filter(request()->all())->permissionCheck()->get();

        // Then ...
        // Result must contains three tickets
        $this->assertCount(3, $results);
        $this->assertEquals([$ticket1->id, $ticket2->id, $ticket3->id], $results->pluck('id')->toArray());
    }

    /** @test */
    public function users_who_have_permissions_can_see_only_related_tickets()
    {
        // Given tickets
        $ticket1 = factory(OurTechnicTicket::class)->create();
        $ticket2 = factory(OurTechnicTicket::class)->create();
        $ticket3 = factory(OurTechnicTicket::class)->create();

        // When we use OurTechnicTicket::filter()
        // as non - principle
        $nonPrimary = User::where('group_id', '!=', 47)->whereNotIn('id', [1, User::HARDCODED_PERSONS['router']])->where('is_deleted', 0)->first() ?? factory(User::class)->create(['group_id' => 7]);
        $this->actingAs($nonPrimary);
        $results = OurTechnicTicket::filter(request()->all())->permissionCheck()->get();

        // Then ...
        // Result must contains nothing
        $this->assertEmpty($results);
    }

    /** @test */
    public function users_who_have_permissions_can_see_only_related_tickets_one_more_time()
    {
        // Given tickets
        $ticket1 = factory(OurTechnicTicket::class)->create();
        $ticket2 = factory(OurTechnicTicket::class)->create();
        $ticket3 = factory(OurTechnicTicket::class)->create();
        // And non - principle user
        $nonPrimary = User::where('group_id', '!=', 47)->whereNotIn('id', [1, User::HARDCODED_PERSONS['router']])->where('is_deleted', 0)->first() ?? factory(User::class)->create(['group_id' => 7]);
        // Add user to tickets
        $ticket1->users()->attach($nonPrimary->id, ['type' => rand(1, 5)]);
        $ticket3->users()->attach($nonPrimary->id, ['type' => rand(1, 5)]);


        // When we use OurTechnicTicket::filter()
        // as non - principle
        $this->actingAs($nonPrimary);
        $results = OurTechnicTicket::filter(request()->all())->permissionCheck()->get();

        // Then ...
        // Result must contains two tickets
        $this->assertCount(2, $results);
        // Specially first and third one
        $this->assertEquals([$ticket1->id, $ticket3->id], $results->pluck('id')->toArray());
    }

    /** @test */
    public function it_udates_usage_time_correctly()
    {
        $ser = new TechnicTicketService();
        $tic = $this->seedTicketsWithUsers(1, [
            'sending_from_date' => Carbon::now(),
            'sending_to_date' => Carbon::now()->addDays(2),
            'getting_from_date' => Carbon::now()->addDays(2),
            'getting_to_date' => Carbon::now()->addDays(3),
            'usage_from_date' => Carbon::now()->addDays(4),
            'usage_to_date' => Carbon::now()->addDays(7),
        ])->first();

        $old_usage = $tic->usage_from_date;

        $attributes = ['getting_to_date' => Carbon::now()->addDays(5)->isoFormat('DD.MM.YYYY')];

        $ser->updateTicket($tic, $attributes);

        $tic->refresh();
        $this->assertEquals(Carbon::now()->addDays(5)->startOfDay(), Carbon::parse($tic->usage_from_date)->startOfDay());
        $this->assertEquals(Carbon::now()->addDays(8)->startOfDay(), Carbon::parse($tic->usage_to_date)->startOfDay());
    }
}

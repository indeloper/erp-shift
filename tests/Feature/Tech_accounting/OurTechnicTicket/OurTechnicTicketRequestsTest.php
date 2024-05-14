<?php

namespace Tests\Feature;

use App\Events\NotificationCreated;
use App\Models\Comment;
use App\Models\FileEntry;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Tech_accounting\OurTechnicTicket\OurTechnicTicketTestCase;

class OurTechnicTicketRequestsTest extends OurTechnicTicketTestCase
{
    protected $authed_user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authed_user = $this->rps->random();
        $this->actingAs($this->authed_user);
    }

    /** @test */
    public function rp_can_accept_ticket_and_task_will_be_closed_and_new_task_will_be_created()
    {
        $this->actingAs(User::first());
        $request = $this->validFields(['resp_rp_user_id' => $this->authed_user->id]);
        $ticket = $this->service->createNewTicket($request);

        $this->actingAs($this->authed_user);
        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'acceptance' => 'confirm',
        ])->assertSessionDoesntHaveErrors();

        $ticket->refresh();

        $this->assertContains('согласована и ожидает назначения на рейс', Notification::latest()->take(3)->get()->last()->name);
        $this->assertEquals($this->authed_user->id, $ticket->comments()->latest()->first()->author_id);
        $this->assertEquals(2, $ticket->status);
        $this->assertEquals(1, $ticket->tasks()->where('status', 28)->first()->is_solved, 'Task is not solved');
        $this->assertEquals(0, $ticket->tasks()->where('status', 30)->first()->is_solved, 'Task solved, but should not be');
    }

    /** @test */
    public function when_rp_create_usage_ticket_it_goes_to_using_directly()
    {
        $request = $this->validFields([
            'sending_from_date' => '',
            'sending_to_date' => '',
            'getting_to_date' => '',
            'getting_from_date' => '',
        ]);
        $this->post(route('building::tech_acc::our_technic_tickets.store'), $request);

        $this->assertEquals(1, OurTechnicTicket::latest()->first()->type);

        $this->assertEquals(5, OurTechnicTicket::first()->status);
    }

    /** @test */
    public function user_can_decline_ticket_and_task_will_be_closed_and_comment_will_be_created()
    {
        $this->actingAs(User::first()); //creating ticket as ivan
        $request = $this->validFields(['resp_rp_user_id' => $this->authed_user->id]);
        $ticket = $this->service->createNewTicket($request);

        $this->actingAs($this->authed_user); //accepting ticket as rp
        $final_note = $this->faker()->sentence;

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'acceptance' => 'reject',
            'final_note' => $final_note,
        ])->assertSessionDoesntHaveErrors();

        $ticket->refresh();

        $this->assertEquals(3, $ticket->status);
        $this->assertEquals($final_note, $ticket->tasks()->where('status', 28)->first()->final_note);
        $this->assertEquals(1, $ticket->tasks()->where('status', 28)->first()->is_solved, 'Task is not solved');
        $this->assertContains('Заявка была отклонена', $ticket->comments()->latest()->first()->comment);
    }

    /** @test */
    public function it_creates_notification_on_ticket_store()
    {
        $this->actingAs(User::find(1)); //rps skips this step
        $technic = factory(OurTechnic::class)->create();
        $request = $this->validFields(['our_technic_id' => $technic->id]);

        $this->expectsEvents(NotificationCreated::class);

        $this->service->createNewTicket($request);

        $this->assertCount(1, $this->firedEvents);
        $this->assertEquals(68, $this->firedEvents[0]->type);

        $this->assertEquals(68, Notification::latest()->first()->type);
    }

    /** @test */
    public function it_attach_vehicles_on_ticket_store()
    {
        $this->actingAs(User::find(1)); //rps skips this step
        $vehicles = factory(OurVehicles::class)->create();
        $request = $this->validFields(['vehicle_ids' => [$vehicles->id]]);

        $ticket = $this->service->createNewTicket($request);

        $this->assertEquals($vehicles->id, $ticket->vehicles()->first()->id);
        $this->assertCount(1, $ticket->vehicles);
    }

    /** @test */
    public function it_gets_all_additional_information_with_show()
    {
        $tickets = $this->seedTicketsWithUsers(3);
        $second_ticket = $tickets[1];

        $response = $this->get(route('building::tech_acc::our_technic_tickets.show', $second_ticket->id))->assertStatus(200);

        $this->assertEquals($second_ticket->users[1]->first_name, $response->json('data.ticket.users.1.first_name'));
        $this->assertEquals($second_ticket->getting_object->name, $response->json('data.ticket.getting_object.name'));
        $this->assertEquals($second_ticket->sending_object->name, $response->json('data.ticket.sending_object.name'));
        $this->assertEquals($second_ticket->our_technic->owner, $response->json('data.ticket.our_technic.owner'));
        $this->assertEquals($second_ticket->our_technic->category->name, $response->json('data.ticket.our_technic.category_name'));
    }

    /** @test */
    public function it_creates_task_for_logist_when_ticket_was_accepted()
    {
        $this->actingAs(User::first());

        $request = $this->validFields([
            'usage_from_date' => '',
            'usage_to_date' => '',
            'resp_rp_user_id' => $this->authed_user->id,
            'process_resp_user_id' => $this->logist->id,
        ]);
        $ticket = $this->service->createNewTicket($request);

        $this->actingAs($this->authed_user); //accepting ticket as rp
        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'acceptance' => 'confirm',
        ])->assertStatus(200);

        $ticket->refresh();

        $logist_task = $ticket->tasks()->where('status', 30)->first();

        $this->assertEquals(2, $ticket->status);
        $this->assertEquals(1, $ticket->tasks()->where('status', 28)->first()->is_solved, 'Task is not solved');
        $this->assertEquals(0, $logist_task->is_solved, 'Task solved, but should not be');
        $this->assertEquals($this->logist->id, $logist_task->responsible_user_id);
    }

    /** @test */
    public function logist_can_accept_ticket()
    {
        $this->actingAs($this->logist);
        $this->withoutExceptionHandling();

        //create ticket with data
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 2])->first();
        $vehicles = factory(OurVehicles::class, 3)->create();
        $ticket->vehicles()->attach($vehicles);
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 30]));

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'vehicle_ids' => $vehicles->first()->id,
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertCount(1, $ticket->vehicles);
        $this->assertEquals(6, $ticket->status);
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 30)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 31)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 32)->get());
    }

    /** @test */
    public function logist_can_hold_ticket()
    {
        $this->actingAs($this->logist);
        $this->withoutExceptionHandling();

        $ticket = $this->seedTicketsWithUsers(1, ['status' => 2])->first();
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 30]));

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'hold',
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(4, $ticket->status);
    }

    /** @test */
    public function logist_can_reject_ticket()
    {
        $this->actingAs($this->logist);
        $this->withoutExceptionHandling();

        $ticket = $this->seedTicketsWithUsers(1, ['status' => 2])->first();
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 30]));

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'reject',
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(3, $ticket->status);
    }

    /** @test */
    public function user_can_confirm_sending_and_close_task_but_still_wait_for_receiving()
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6])->first();
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31]));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $sender = $ticket->users()->ofType('request_resp_user_id')->first();
        $this->actingAs($sender);

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'task_status' => $this->service->responsible_user_task_status_map['request_resp_user_id'],

        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(6, $ticket->status);
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 31)->get());
        $this->assertEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 31)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 32)->get());
    }

    /** @test */
    public function when_user_confirm_sending_notification_is_sent()
    {
        $this->withoutExceptionHandling();
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6])->first();
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31, 'name' => 'Подтверждение отправки техники']));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $sender = $ticket->users()->ofType('request_resp_user_id')->first();
        $this->actingAs($sender);

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'task_status' => $this->service->responsible_user_task_status_map['request_resp_user_id'],

        ])->assertSessionDoesntHaveErrors()->assertStatus(200);

        $this->assertEquals("По заявке №{$ticket->id} зафиксирована отправка", Notification::where('type', 71)->first()->name);
    }

    /** @test */
    public function user_can_confirm_sending_and_close_task_but_still_wait_for_receiving_even_it_is_one_person()
    {
        $moving_resp = $this->rps_and_prorabs->random()->id;
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6], [
            'request_resp_user_id' => $moving_resp,
            'recipient_user_id' => $moving_resp,
        ])->first();
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31]));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $sender = $ticket->users()->ofType('request_resp_user_id')->first();
        $this->actingAs($sender);

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'task_status' => $this->service->responsible_user_task_status_map['request_resp_user_id'],
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(6, $ticket->status);
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 31)->get());
        $this->assertEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 31)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 32)->get());
    }

    /** @test */
    public function user_can_confirm_recieving_and_close_task_but_still_wait_for_sending()
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6])->first();
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31]));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $recipient = $ticket->users()->ofType('recipient_user_id')->first();
        $this->actingAs($recipient);

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'task_status' => $this->service->responsible_user_task_status_map['recipient_user_id'],
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(6, $ticket->status);
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 32)->get());
        $this->assertEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 32)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 31)->get());
    }

    /** @test */
    public function moving_complete_only_when_both_users_confirm()
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6], [
            'resp_rp_user_id' => $this->rps->first()->id,
            'request_resp_user_id' => $this->rps->first()->id,
            'recipient_user_id' => $this->rps->first()->id,
            'usage_resp_user_id' => $this->rps->first()->id,
            'author_user_id' => Auth::id(),
        ])->first();
        //assuming that sending was done already
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31, 'is_solved' => 1]));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $recipient = $ticket->users()->ofType('recipient_user_id')->first();
        $this->actingAs($recipient);

        //so we confirming getting
        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'task_status' => $this->service->responsible_user_task_status_map['recipient_user_id'],
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(5, $ticket->status);
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 32)->get());
        $this->assertEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 32)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 31)->get());
    }

    /** @test */
    public function moving_complete_only_when_both_users_confirm_and_technic_will_change_location()
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6], [
            'resp_rp_user_id' => $this->rps->first()->id,
            'request_resp_user_id' => $this->rps->first()->id,
            'recipient_user_id' => $this->rps->first()->id,
            'usage_resp_user_id' => $this->rps->first()->id,
            'author_user_id' => Auth::id(),
        ])->first();
        //assuming that sending was done already
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31, 'is_solved' => 1]));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $old_location = $ticket->our_technic->start_location;
        $recipient = $ticket->users()->ofType('recipient_user_id')->first();
        $this->actingAs($recipient);

        //so we confirming getting
        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'task_status' => $this->service->responsible_user_task_status_map['recipient_user_id'],
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(5, $ticket->status);

        $this->assertNotEquals($old_location, $ticket->our_technic->start_location);

        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 32)->get());
        $this->assertEmpty($ticket->tasks()->where('is_solved', 0)->where('status', 32)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 31)->get());
    }

    /** @test */
    public function user_can_confirm_sending_and_comment_will_be_created()
    {
        $this->withoutExceptionHandling();
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6])->first();
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31]));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $sender = $ticket->users()->ofType('request_resp_user_id')->first();
        $files = factory(FileEntry::class, 3)->create();

        $this->actingAs($sender);

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
            'comment' => $this->faker()->sentence,
            'file_ids' => $files->pluck('id')->toArray(),
            'task_status' => 31,
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $comment = Comment::latest()->first();
        //        dd(FileEntry::all());
        $this->assertEquals(6, $ticket->status);
        $this->assertEquals($files->count(), $comment->files()->count());
    }

    /** @test */
    public function it_can_be_returned_to_logist()
    {
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 6], [
            'resp_rp_user_id' => $this->rps->first()->id,
            'request_resp_user_id' => $this->rps->first()->id,
            'recipient_user_id' => $this->rps->first()->id,
            'usage_resp_user_id' => $this->rps->first()->id,
            'author_user_id' => Auth::id(),
        ])->first();
        //assuming that sending was done already
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 31]));
        $ticket->tasks()->create(factory(Task::class)->raw(['status' => 32]));

        $recipient = $ticket->users()->ofType('recipient_user_id')->first();
        $this->actingAs($recipient);

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'rollback',
            'comment' => $this->faker()->sentence,
        ])->assertSessionDoesntHaveErrors()->assertStatus(200);
        $ticket->refresh();

        $this->assertEquals(2, $ticket->status);
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 32)->get());
        $this->assertNotEmpty($ticket->tasks()->where('is_solved', 1)->where('status', 31)->get());
    }

    /** @test */
    public function rp_can_accept_ticket_and_choose_process_resp_user()
    {
        $this->actingAs(User::first());
        $request = $this->validFields(['resp_rp_user_id' => $this->authed_user->id, 'process_resp_user_id' => '']);
        $ticket = $this->service->createNewTicket($request);

        $this->actingAs($this->authed_user);
        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'acceptance' => 'confirm',
            'process_resp_user_id' => $this->logist->id,
        ])->assertSessionDoesntHaveErrors();

        $ticket->refresh();

        $this->assertEquals($this->logist->id, $ticket->getResponsibleType('process_resp_user_id')->id);
    }

    /** @test */
    public function it_creates_task_for_resp_if_today_is_first_day_of_usage()
    {
        $usage_user = $this->rps->first();
        $this->actingAs($usage_user);
        $ticket = $this->seedTicketsWithUsers(1, ['status' => 5], [
            'resp_rp_user_id' => $usage_user->id,
            'request_resp_user_id' => $usage_user->id,
            'recipient_user_id' => $usage_user->id,
            'usage_resp_user_id' => $usage_user->id,
            'author_user_id' => Auth::id(),
        ])->first();
        //assuming that sending was done already

        $this->put(route('building::tech_acc::our_technic_tickets.update', $ticket->id), [
            'result' => 'confirm',
        ])->assertOk();
        $ticket->refresh();

        $this->assertEquals(7, $ticket->status);

        $this->assertNotEmpty($usage_user->tasks, 'There is no task, but has to be one');

        $this->assertEquals('Отметка времени использования техники за '.Carbon::now()->isoFormat('DD.MM.YYYY'), $usage_user->tasks()->first()->name);
    }
}

<?php

namespace Tests\Feature\Tech_accounting\OurTechnicTicket;

use App\Models\Task;
use App\Models\TechAcc\OurTechnicTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OutTechnicTicketActionsTest extends OurTechnicTicketTestCase
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
        $this->ourTechnicTicket = $this->seedTicketsWithUsers(1, ['status' => 5], ['usage_resp_user_id' => Auth::user()->id])->first();
        $this->response_user = $this->ourTechnicTicket->users()->wherePivot('type', 4)->first();
    }

    /** @test */
    public function it_can_close_ticket()
    {
        $fakeComment = $this->faker->text(100);

        $this->post(route('building::tech_acc::our_technic_tickets.close', $this->ourTechnicTicket->id), [
            'comment' => $fakeComment,
        ]);

        $this->assertEquals('8', OurTechnicTicket::find($this->ourTechnicTicket->id)->status);
        $this->assertEquals($this->ourTechnicTicket->comments()->latest()->first()->comment, 'Отметка об окончании использования техники. Комментарий пользователя: '.$fakeComment);
    }

    /** @test */
    public function it_can_request_ticket_extension()
    {
        $fakeComment = $this->faker->text(100);

        $this->post(route('building::tech_acc::our_technic_tickets.request_extension', $this->ourTechnicTicket->id), [
            'usage_to_date' => Carbon::now()->addDays(4)->format('d.m.Y H:i'),
            'comment' => $fakeComment,
        ]);

        $this->assertEquals($this->ourTechnicTicket->comments()->latest()->first()->comment, 'Запрос продления использования техники. Комментарий пользователя: '.$fakeComment);
        $this->assertEquals(Task::latest()->first()->id, $this->ourTechnicTicket->latest()->first()->tasks->first()->id);
    }

    /** @test */
    public function it_agree_ticket_extension()
    {
        $fakeComment = $this->faker->text(100);
        $new_date = Carbon::now()->addDays(6);

        $comment = $this->ourTechnicTicket->comments()->create([
            'comment' => 'Запрос продления использования техники. Комментарий пользователя: '.$fakeComment,
            'user_id' => Auth::user()->id,
        ]);

        $task = $this->ourTechnicTicket->tasks()->create([
            'name' => 'Запрос продления использования техники',
            'description' => $comment->comment,
            'responsible_user_id' => $this->ourTechnicTicket->users()->wherePivot('type', 1)->first()->id,
            'expired_at' => Carbon::now()->addHours(8),
            'status' => 27,
        ]);

        $task->changing_fields()->create([
            'field_name' => 'usage_to_date',
            'value' => $new_date,
        ]);

        $this->actingAs($this->ourTechnicTicket->users()->wherePivot('type', 1)->first());

        $this->post(route('building::tech_acc::our_technic_tickets.agree_extension', [$task->taskable->id]), [
            'agree' => true,
        ]);
        $this->ourTechnicTicket->fresh();

        $this->assertEquals($this->ourTechnicTicket->tasks()->where('status', 27)->first()->is_solved, 1);
        $this->assertEquals($this->ourTechnicTicket->tasks()->where('status', 27)->first()->get_result, 'Запрос продления использования техники одобрен.');
        $this->assertEquals(OurTechnicTicket::find($this->ourTechnicTicket->id)->usage_to_date, $new_date->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_disagree_ticket_extension()
    {
        $fakeComment = $this->faker->text(100);
        $new_date = Carbon::now()->addDays(4);

        $comment = $this->ourTechnicTicket->comments()->create([
            'comment' => 'Запрос продления использования техники. Комментарий пользователя: '.$fakeComment,
            'user_id' => Auth::user()->id,
        ]);

        $task = $this->ourTechnicTicket->tasks()->create([
            'name' => 'Запрос продления использования техники',
            'description' => $comment->comment,
            'responsible_user_id' => $this->ourTechnicTicket->users()->wherePivot('type', 1)->first()->id,
            'expired_at' => Carbon::now()->addHours(8),
            'status' => 27,
        ]);

        $task->changing_fields()->create([
            'field_name' => 'usage_to_date',
            'value' => $new_date,
        ]);

        $this->actingAs($this->ourTechnicTicket->users()->wherePivot('type', 1)->first());

        $this->post(route('building::tech_acc::our_technic_tickets.agree_extension', [$task->taskable->id]), [
            'agree' => false,
            'final_note' => $fakeComment.'hi',
        ]);

        $this->assertEquals($this->ourTechnicTicket->tasks()->where('status', 27)->first()->is_solved, 1);
        $this->assertEquals($this->ourTechnicTicket->tasks()->where('status', 27)->first()->final_note, $fakeComment.'hi');
        $this->assertNotEquals(OurTechnicTicket::find($this->ourTechnicTicket->id)->usage_to_date, $new_date->format('Y-m-d H:i:s'));
    }
}

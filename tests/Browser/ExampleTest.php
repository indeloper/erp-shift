<?php

namespace Tests\Browser;

use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorContact;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\Task;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function login(): void
    {

    }

    public function testNewCall(): void
    {
        $user = User::factory()->create();

        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret')
                ->press('.btn')
                ->assertPathIs('/users');
        });

        $this->contractor = Contractor::factory()->create();
        $this->contact = ContractorContact::factory()->create(['contractor_id' => $this->contractor->id]);
        $this->object = ProjectObject::factory()->create();
        $this->project = Project::factory()->create(['contractor_id' => $this->contractor, 'object_id' => $this->object]);
        $this->task = Task::factory()->create(['responsible_user_id' => $user->id]);

        $this->browse(function ($browser) {
            $browser->visit('/tasks/new_call/'.$this->task->id.'?contractor_id='.$this->contractor->id.'&contact_id='.$this->contact->id.'&project_id='.$this->project->id)
                ->type('final_note', 'Положительный тестовый звонок')
                ->select('status_result', '1')
                ->press('#close_call_button')
                ->assertPathIs('/tasks');
        });
    }

    public function testQuestionnaire(): void
    {
        $task = Task::orderBy('id', 'desc')->first();

    }
}

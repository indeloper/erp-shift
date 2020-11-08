<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use App\Models\User;
use App\Models\Contractors\Contractor;
use App\Models\ProjectObject;
use App\Models\Project;
use App\Models\Contractors\ContractorContact;
use App\Models\Task;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */

    public function login()
    {

    }


    public function testNewCall()
    {
        $user = factory(User::class)->create();

        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'secret')
                    ->press('.btn')
                    ->assertPathIs('/users');
        });

        $this->contractor = factory(Contractor::class)->create();
        $this->contact = factory(ContractorContact::class)->create(['contractor_id' => $this->contractor->id]);
        $this->object = factory(ProjectObject::class)->create();
        $this->project = factory(Project::class)->create(['contractor_id' => $this->contractor, 'object_id' => $this->object]);
        $this->task = factory(Task::class)->create(['responsible_user_id' => $user->id]);

        $this->browse(function ($browser) {
            $browser->visit('/tasks/new_call/' . $this->task->id . '?contractor_id=' . $this->contractor->id . '&contact_id=' . $this->contact->id . '&project_id=' . $this->project->id)
                ->type('final_note', 'Положительный тестовый звонок')
                ->select('status_result', '1')
                ->press('#close_call_button')
                ->assertPathIs('/tasks');
        });
    }

    public function testQuestionnaire()
    {
        $task = Task::orderBy('id', 'desc')->first();

    }
}

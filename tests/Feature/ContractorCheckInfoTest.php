<?php

namespace Tests\Feature;

use App\Console\Commands\CheckContractorsInfo;
use App\Models\Contractors\Contractor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ContractorCheckInfoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::where('group_id', 7)->first());
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRunCommand()
    {
        $this->artisan('check:contractors')
//            ->expectsOutput('0 errors')
            ->assertExitCode(0);
    }

    public function testCreateTask()
    {
        $countUserTasks = Auth::user()->tasks()->count();
        $countUserNotifications = Auth::user()->notifications()->count();

        $contractor = factory(Contractor::class)->create();
        $changingFields = [];
        $changingFields[] = ['field_name' => 'full_name', 'value' => $this->faker->company, 'old_value' => $contractor->full_name];
        $changingFields[] = ['field_name' => 'inn', 'value' => random_int(89000000000, 89999999999), 'old_value' => $contractor->inn];

        (new CheckContractorsInfo)->createTask($contractor, $changingFields, Auth::user()->id);

        $updatedCountUserTasks = Auth::user()->tasks()->count();
        $updatedCountUserNotifications = Auth::user()->notifications()->count();
        $countOfChangingFilelds = Auth::user()->tasks()->orderBy('id', 'desc')->first()->changing_fields()->count();

        $this->assertEquals($countOfChangingFilelds, 2);
        $this->assertEquals($countUserTasks + 1, $updatedCountUserTasks);
        $this->assertEquals($countUserNotifications + 1, $updatedCountUserNotifications);
    }

    public function testCreateTaskNotification()
    {
        $contractor = factory(Contractor::class)->create();
        $changingFields = [];
        $changingFields[] = ['field_name' => 'full_name', 'value' => $this->faker->company, 'old_value' => $contractor->full_name];
        $changingFields[] = ['field_name' => 'inn', 'value' => random_int(89000000000, 89999999999), 'old_value' => $contractor->inn];

        (new CheckContractorsInfo)->createTask($contractor, $changingFields, Auth::user()->id);

        $task = Auth::user()->tasks()->orderBy('id', 'desc')->first();
        $notification = Auth::user()->notifications()->orderBy('id', 'desc')->first();

        $this->assertEquals($notification->task_id, $task->id);
    }
}

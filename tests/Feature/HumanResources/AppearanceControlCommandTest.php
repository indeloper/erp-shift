<?php

namespace Tests\Feature\HumanResources;

use App\Models\{Contract\Contract, Group, Notification, Project, ProjectResponsibleUser, Task, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppearanceControlCommandTest extends TestCase
{
    /** @test */
    public function when_we_execute_command_at_8AM_without_projects_nothing_should_happen()
    {
        // Given no projects
        Project::query()->delete();
        // Given notifications count
        $notificationsCount = Notification::count();
        // Given tasks count
        $tasksCount = Task::count();

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then nothing should happen
        // Notifications count should be the same
        $this->assertEquals(Notification::count(), $notificationsCount);
        // Tasks count should be the same
        $this->assertEquals(Task::count(), $tasksCount);
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_without_contract_work_start_nothing_should_happen()
    {
        /**
         * Contract work start equals true if project has more than one contract in status 5 or 6 (На гарантии или Подписан)
         */
        // Clear projects
        Project::query()->delete();
        // Given project without time responsible user and contracts
        $project = factory(Project::class)->create(['time_responsible_user_id' => null]);

        // Given notifications count
        $notificationsCount = Notification::count();
        // Given tasks count
        $tasksCount = Task::count();

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then nothing should happen
        // Notifications count should be the same
        $this->assertEquals(Notification::count(), $notificationsCount);
        // Tasks count should be the same
        $this->assertEquals(Task::count(), $tasksCount);
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_with_contract_work_but_without_people_on_project_start_nothing_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given project without time responsible user and people on project
        $project = factory(Project::class)->create(['time_responsible_user_id' => null]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);

        // Given notifications count
        $notificationsCount = Notification::count();
        // Given tasks count
        $tasksCount = Task::count();

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then nothing should happen
        // Notifications count should be the same
        $this->assertEquals(Notification::count(), $notificationsCount);
        // Tasks count should be the same
        $this->assertEquals(Task::count(), $tasksCount);
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_with_contracts_work_and_workers_but_without_time_responsible_user_and_without_RPs_some_things_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given project without time responsible user and RPs
        $project = factory(Project::class)->create(['time_responsible_user_id' => null]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        $project->users()->saveMany(factory(User::class, 5)->create());
        // Given main engineer
        $mainEngineer = Group::find(8)->getUsers()->first();

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 39 status
        $tasks = $project->tasks()->where('status', 39)->get();
        $this->assertCount(1, $tasks);
        // For main engineer
        $this->assertEquals([$mainEngineer->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for Main Engineer
        $notifications = $tasks->first()->notifications->where('type', 100);
        $this->assertEquals([$mainEngineer->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Назначение ответственного за учёт времени в проекте»");
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_with_contracts_work_and_workers_but_without_time_responsible_user_and_with_pile_RP_some_things_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given project without time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => null]);
        // Given pile RP
        $rp = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rp->id, 'role' => 5]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        $project->users()->saveMany(factory(User::class, 5)->create());

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 39 status
        $tasks = $project->tasks()->where('status', 39)->get();
        $this->assertCount(1, $tasks);
        // For RP
        $this->assertEquals([$rp->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for RP
        $notifications = $tasks->first()->notifications->where('type', 100);
        $this->assertEquals([$rp->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Назначение ответственного за учёт времени в проекте»");
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_with_contracts_work_and_workers_but_without_time_responsible_user_and_with_tongue_RP_some_things_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given project without time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => null]);
        // Given tongue RP
        $rp = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rp->id, 'role' => 6]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        $project->users()->saveMany(factory(User::class, 5)->create());

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 39 status
        $tasks = $project->tasks()->where('status', 39)->get();
        $this->assertCount(1, $tasks);
        // For RP
        $this->assertEquals([$rp->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for RP
        $notifications = $tasks->first()->notifications->where('type', 100);
        $this->assertEquals([$rp->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Назначение ответственного за учёт времени в проекте»");
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_with_contracts_work_and_workers_but_without_time_responsible_user_and_with_both_directions_RPs_some_things_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given project without time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => null]);
        // Given tongue RP
        $rpTongue = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpTongue->id, 'role' => 6]);
        // Given pile RP
        $rpPile = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpPile->id, 'role' => 5]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        $project->users()->saveMany(factory(User::class, 5)->create());

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 39 status
        $tasks = $project->tasks()->where('status', 39)->get();
        $this->assertCount(2, $tasks);
        // For RPs
        $this->assertEquals([$rpTongue->id, $rpPile->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for RPs
        $notifications = Notification::where('type', 100)->get();
        $this->assertEquals([$rpTongue->id, $rpPile->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Назначение ответственного за учёт времени в проекте»");
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_with_contracts_work_and_workers_and_time_responsible_user_some_things_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        $project->users()->saveMany(factory(User::class, 5)->create());

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        // For time responsible user
        $this->assertEquals([$timeResponsibleUser->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 101)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Контроль явки»");
    }

    /** @test */
    public function when_we_execute_command_at_8AM_with_projects_with_contracts_work_and_time_responsible_user_without_workers_nothing_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user but without workers
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given notifications count
        $notificationsCount = Notification::count();
        // Given tasks count
        $tasksCount = Task::count();

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then nothing should happen
        // Notifications count should be the same
        $this->assertEquals(Notification::count(), $notificationsCount);
        // Tasks count should be the same
        $this->assertEquals(Task::count(), $tasksCount);
    }

    /** @test */
    public function when_we_execute_command_at_9AM_without_tasks_and_projects_nothing_should_happen()
    {
        // Clear projects, tasks and notifications
        Project::query()->delete();
        Task::query()->delete();
        Notification::query()->delete();

        // When we call command at 9AM
        $this->artisan('appearance:control', ['time' => '9:00'])->run();

        // Then ...
        // Nothing should happen
        $this->assertEquals(0, Notification::count());
        $this->assertEquals(0, Task::count());
    }

    /** @test */
    public function when_we_execute_command_at_9AM_with_tasks_and_projects_something_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given tongue RP
        $rpTongue = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpTongue->id, 'role' => 6]);
        // Given pile RP
        $rpPile = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpPile->id, 'role' => 5]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        $project->users()->saveMany(factory(User::class, 5)->create());

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        // For time responsible user
        $this->assertEquals([$timeResponsibleUser->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 101)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Контроль явки»");

        // When then we call command at 9AM
        $this->artisan('appearance:control', ['time' => '9:00'])->run();

        // Then ...
        // Some notifications should be generated for time responsible user and RPs
        $notifications = Notification::where('type', 2)->get();
        $this->assertEquals([$timeResponsibleUser->id, $rpTongue->id, $rpPile->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Задача «Контроль явки» просрочена");
    }

    /** @test */
    public function when_we_execute_command_at_9AM_with_tasks_and_projects_without_RPs_something_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        $project->users()->saveMany(factory(User::class, 5)->create());

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        // For time responsible user
        $this->assertEquals([$timeResponsibleUser->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 101)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Контроль явки»");

        // When then we call command at 9AM
        $this->artisan('appearance:control', ['time' => '9:00'])->run();

        // Then ...
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 2)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Задача «Контроль явки» просрочена");
    }

    /** @test */
    public function when_we_execute_command_at_9AM_with_yesterday_tasks_and_projects_nothing_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given task for project (imitate yesterday task)
        $task = factory(Task::class)->create(['project_id' => $project->id, 'status' => 40, 'created_at' => now()->subDay()]);

        // When then we call command at 9AM
        $this->artisan('appearance:control', ['time' => '9:00'])->run();

        // Then ...
        // Nothing should happen
        $this->assertEquals(0, Notification::whereType(2)->count());
    }

// Code blocks below are commented out because of requirements change
/*
    public function when_we_execute_command_at_5PM_without_tasks_and_projects_nothing_should_happen()
    {
        // Clear projects, tasks and notifications
        Project::query()->delete();
        Task::query()->delete();
        Notification::query()->delete();

        // When we call command at 9AM
        $this->artisan('appearance:control', ['time' => '17:00'])->run();

        // Then ...
        // Nothing should happen
        $this->assertEquals(0, Notification::count());
        $this->assertEquals(0, Task::count());
    }

    public function when_we_execute_command_at_5PM_with_only_solved_tasks_nothing_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given task for project
        $task = factory(Task::class)->create(['project_id' => $project->id, 'status' => 12]);

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        // For time responsible user
        $this->assertEquals([$timeResponsibleUser->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 101)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Контроль явки»");

        // When we solve task
        $tasks->first()->solve();
        // And the we call command at 5PM
        $this->artisan('appearance:control', ['time' => '17:00'])->run();

        // Then ...
        // Nothing should happen
        $this->assertEquals(0, Notification::whereType(102)->count());
    }

    public function when_we_execute_command_at_5PM_with_unsolved_tasks_and_without_RPs_something_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given task for project
        $task = factory(Task::class)->create(['project_id' => $project->id, 'status' => 12]);

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        // For time responsible user
        $this->assertEquals([$timeResponsibleUser->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 101)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Контроль явки»");

        // When we call command at 5PM
        $this->artisan('appearance:control', ['time' => '17:00'])->run();

        // Then ...
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 102)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Задача «Контроль явки» не выполнена и закрыта системой");
        // And task should be closed
        $this->assertEquals(1, $tasks->first()->refresh()->is_solved);
    }

    public function when_we_execute_command_at_5PM_with_unsolved_tasks_and_with_RPs_something_should_happen()
    {
        // Clear projects
        Project::query()->delete();
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given tongue RP
        $rpTongue = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpTongue->id, 'role' => 6]);
        // Given pile RP
        $rpPile = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpPile->id, 'role' => 5]);
        // Given task for project
        $task = factory(Task::class)->create(['project_id' => $project->id, 'status' => 12]);

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();

        // Then ...
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        // For time responsible user
        $this->assertEquals([$timeResponsibleUser->id], $tasks->pluck('responsible_user_id')->toArray());
        // Some notifications should be generated for time responsible user
        $notifications = Notification::where('type', 101)->get();
        $this->assertEquals([$timeResponsibleUser->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Новая задача «Контроль явки»");

        // When we call command at 5PM
        $this->artisan('appearance:control', ['time' => '17:00'])->run();

        // Then ...
        // Some notifications should be generated for time responsible user and RPs
        $notifications = Notification::where('type', 102)->get();
        $this->assertEquals([$timeResponsibleUser->id, $rpTongue->id, $rpPile->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Задача «Контроль явки» не выполнена и закрыта системой");
        // And task should be closed
        $this->assertEquals(1, $tasks->first()->refresh()->is_solved);
    }
*/
// UP TO HERE
}
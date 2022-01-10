<?php

namespace Tests\Feature\HumanResources;

use App\Models\HumanResources\Brigade;
use App\Models\Project;
use App\Models\ProjectResponsibleUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function project_can_have_time_responsible_user()
    {
        // Given fresh user
        $newUser = factory(User::class)->create();
        // Given object with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $newUser->id]);

        // Then project should have timeResponsible() relation
        $this->assertInstanceOf(User::class, $project->timeResponsible);
        $this->assertEquals($newUser->id, $project->timeResponsible->id);
    }

    /** @test */
    public function user_without_permission_cannot_update_project_time_responsible_user()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given fresh user
        $newUser = factory(User::class)->create();

        // When user make any post request
        $data = [];
        $response = $this->actingAs($user)->post(route('projects::update_time_responsible', $project->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_assign_new_project_time_responsible_user()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given fresh user
        $newUser = factory(User::class)->create();

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'time_responsible_user_id' => $newUser->id,
        ];
        $response = $this->actingAs($user)->post(route('projects::update_time_responsible'), $data);

        // Then ...
        // Project should have timeResponsible() relation
        $this->assertInstanceOf(User::class, $project->refresh()->timeResponsible);
        $this->assertEquals($newUser->id, $project->timeResponsible->id);
        // Project should have logs
        $this->assertCount(1, $project->logs);
        // New user should have notification
        $notification = $newUser->refresh()->notifications->first();
        $this->assertEquals(90, $notification->type);
        $this->assertEquals("Вы были назначены на позицию ответственного за учёт рабочего времени в проекте {$project->name}!", $notification->name);
    }

    /** @test */
    public function user_with_permission_can_remove_project_time_responsible_user()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'time_responsible_user_id' => null,
        ];
        $response = $this->actingAs($user)->post(route('projects::update_time_responsible'), $data);

        // Then ...
        // Project should loose timeResponsible() relation
        $this->assertEmpty($project->refresh()->timeResponsible);
        // Project should have logs
        $this->assertCount(1, $project->logs);
        // New user should have notification
        $notification = $user->refresh()->notifications->last();
        $this->assertEquals(91, $notification->type);
        $this->assertEquals("Вы были сняты с позиции ответственного за учёт рабочего времени в проекте {$project->name}!", $notification->name);
    }

    /** @test */
    public function user_with_permission_can_change_project_time_responsible_user()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given new user
        $newUser = factory(User::class)->create();

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'time_responsible_user_id' => $newUser->id,
        ];
        $response = $this->actingAs($user)->post(route('projects::update_time_responsible'), $data);

        // Then ...
        // Project should have timeResponsible() relation
        $this->assertInstanceOf(User::class, $project->refresh()->timeResponsible);
        $this->assertEquals($newUser->id, $project->timeResponsible->id);
        // Project should have logs
        $this->assertCount(1, $project->logs);
        // Old user should have notification
        $notification = $user->refresh()->notifications->last();
        $this->assertEquals(91, $notification->type);
        $this->assertEquals("Вы были сняты с позиции ответственного за учёт рабочего времени в проекте {$project->name}!", $notification->name);
        // New user should have notification
        $notification = $newUser->refresh()->notifications->first();
        $this->assertEquals(90, $notification->type);
        $this->assertEquals("Вы были назначены на позицию ответственного за учёт рабочего времени в проекте {$project->name}!", $notification->name);
    }

    /** @test */
    public function after_time_responsible_user_change_new_time_responsible_should_have_tasks_from_old_time_responsible()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given new user
        $newUser = factory(User::class)->create();
        // Given appearance control task (imitation)
        $appearanceTask = factory(Task::class)->create(['project_id' => $project->id, 'status' => 40, 'responsible_user_id' => $user]);
        // Given work time control task (imitation)
        $workTimeTasks = factory(Task::class)->create(['project_id' => $project->id, 'status' => 41, 'responsible_user_id' => $user]);
        // Given solved appearance control task (imitation)
        $solvedTask = factory(Task::class)->create(['project_id' => $project->id, 'status' => 40, 'responsible_user_id' => $user, 'is_solved' => 1]);
        // Given solved work time control task (imitation)
        $solvedTask = factory(Task::class)->create(['project_id' => $project->id, 'status' => 41, 'responsible_user_id' => $user, 'is_solved' => 1]);
        // Given any solved task (imitation)
        $solvedTask = factory(Task::class)->create(['project_id' => $project->id, 'status' => 666, 'responsible_user_id' => $user]);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'time_responsible_user_id' => $newUser->id,
        ];
        $response = $this->actingAs($user)->post(route('projects::update_time_responsible'), $data);

        // Then ...
        // Project should have timeResponsible() relation
        $this->assertInstanceOf(User::class, $project->refresh()->timeResponsible);
        $this->assertEquals($newUser->id, $project->timeResponsible->id);
        // Project should have logs
        $this->assertCount(1, $project->logs);
        // Old user should have notification
        $notification = $user->refresh()->notifications->last();
        $this->assertEquals(91, $notification->type);
        $this->assertEquals("Вы были сняты с позиции ответственного за учёт рабочего времени в проекте {$project->name}!", $notification->name);
        // New user should have notification
        $notification = $newUser->refresh()->notifications->first();
        $this->assertEquals(90, $notification->type);
        $this->assertEquals("Вы были назначены на позицию ответственного за учёт рабочего времени в проекте {$project->name}!", $notification->name);
        // Also new user should have appearance tasks from old time responsible user (only active one)
        $newUserAppearanceTasks = $newUser->tasks->where('status', 40);
        $this->assertEquals($newUserAppearanceTasks->pluck('id')->toArray(), [$appearanceTask->id]);
        // Also new user should have work time tasks from old time responsible user (only active one)
        $newUserWorkTimeTasks = $newUser->tasks->where('status', 41);
        $this->assertEquals($newUserWorkTimeTasks->pluck('id')->toArray(), [$workTimeTasks->id]);
    }

    /** @test */
    public function project_can_have_appointment_users()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user
        $user = factory(User::class)->create();

        // When we add user on project
        $project->users()->save($user);

        // Then project should have users() relation with count 1
        $this->assertCount(1, $project->refresh()->users);
        $this->assertEquals($user->id, $project->users[0]->id);
    }

    /** @test */
    public function user_cannot_be_appointed_to_project_by_post_by_user_without_permission()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request with any data
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_cannot_be_appointed_to_project_by_post_by_user_with_permission_but_without_roles()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request with any data
        $data = [
            'project_id' => $project->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_can_be_appointed_to_project_by_post_by_user_with_permission_and_time_manager_role()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $user->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);

        // Then ...
        // Project should have users() relation with count 1
        $this->assertCount(1, $project->refresh()->users);
        $this->assertEquals($user->id, $project->users[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $project->users->pluck('id'));
    }

    /** @test */
    public function user_can_be_appointed_to_project_by_post_by_user_with_permission_and_role_in_project()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given use role in project
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 8]);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $user->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);

        // Then ...
        // Project should have users() relation with count 1
        $this->assertCount(1, $project->refresh()->users);
        $this->assertEquals($user->id, $project->users[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $project->users->pluck('id'));
    }

    /** @test */
    public function user_can_not_be_appointed_to_project_by_post_by_user_with_permission_and_role_in_project_if_user_already_was_here()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given use role in project
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 8]);
        // Appoint user to project
        $project->users()->save($user);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $user->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('name');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('name')[0], 'Пользователь уже назначен на этот проект, его нельзя назначить ещё раз');
    }

    /** @test */
    public function user_cannot_be_detached_from_object_by_post_by_user_without_permission()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request with any data
        $response = $this->actingAs($user)->post(route('projects::detach_user', $project->id), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_cannot_be_detached_from_project_by_post_by_user_with_permission_but_without_roles()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request with any data
        $data = [
            'project_id' => $project->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_user', $project->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_cannot_be_detached_from_project_by_post_by_user_with_permission_with_roles_if_user_was_not_attached_to_project()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $user->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_user', $project->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors('user');
    }

    /** @test */
    public function user_can_be_detached_from_project_by_post_by_user_with_permission_and_time_manager_role()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given user attachment
        $userForWork = factory(User::class)->create();
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);
        // Then ...
        // Project should have users() relation with count 1
        $this->assertCount(1, $project->refresh()->users);
        $this->assertEquals($userForWork->id, $project->users[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $project->users->pluck('id'));
        $appointment = $project->appointments[0];

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_user', $project->id), $data);

        // Then ...
        // Object should have users() relation with count 0
        $this->assertCount(0, $project->refresh()->users);
        // And appointment should be deleted
        $this->assertCount(0, $project->appointments);
        // With logs
        $this->assertCount(2, $appointment->refresh()->logs);
    }

    /** @test */
    public function user_can_be_detached_from_project_by_post_by_user_with_permission_and_role_in_project()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given use role in project
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 8]);
        // Given user attachment
        $userForWork = factory(User::class)->create();
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);
        // Then ...
        // Project should have users() relation with count 1
        $this->assertCount(1, $project->refresh()->users);
        $this->assertEquals($userForWork->id, $project->users[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $project->users->pluck('id'));
        $appointment = $project->appointments[0];

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_user', $project->id), $data);

        // Then ...
        // Project should have users() relation with count 0
        $this->assertCount(0, $project->refresh()->users);
        // And appointment should be deleted
        $this->assertCount(0, $project->appointments);
        // With logs
        $this->assertCount(2, $appointment->refresh()->logs);
    }

    /** @test */
    public function if_user_had_appoints_to_two_projects_and_after_was_detached_from_one_he_still_should_have_one_appointment()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given projects with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        $project2 = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given user attachment
        $userForWork = factory(User::class)->create();
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);
        $data = [
            'project_id' => $project2->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project2->id), $data);

        // Then user should have two appointments
        $this->assertCount(2, $userForWork->refresh()->appointments);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_user', $project->id), $data);

        // Then ...
        // Object should have users() relation with count 0
        $this->assertCount(0, $project->refresh()->users);
        // Second project should have users() relation with count 1
        $this->assertCount(1, $project2->refresh()->users);
        // And user should have appointments() relation with count 1
        $this->assertCount(1, $userForWork->refresh()->appointments);
    }

    /** @test */
    public function if_project_had_two_users_and_one_was_detached_project_should_have_one_user()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given user attachment
        $userForWork = factory(User::class)->create();
        $secondUserForWork = factory(User::class)->create();
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);
        $data = [
            'project_id' => $project->id,
            'user_id' => $secondUserForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_user', $project->id), $data);

        // Then project should have two users
        $this->assertCount(2, $project->refresh()->users);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->reverse()->pluck('id'), $project->users->pluck('id'));

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'user_id' => $userForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_user', $project->id), $data);

        // Then ...
        // Project should have users() relation with count 1
        $this->assertCount(1, $project->refresh()->users);
        // And user should have appointments() relation with count 1
        $this->assertCount(1, $secondUserForWork->refresh()->appointments);
    }

    /** @test */
    public function project_users_getter_can_return_404()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request without data
        $response = $this->actingAs($user)->post(route('projects::get_project_users'), []);

        // Then 404 should be returned
        $response->assertNotFound();
    }

    /** @test */
    public function project_users_getter_can_return_project_without_users()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();

        // When user make post request with data
        $response = $this->actingAs($user)->post(route('projects::get_project_users'), ['project_id' => $project->id])->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have project key
        $this->assertTrue(array_key_exists('project', $response));
        // This array must have users key
        $this->assertTrue(array_key_exists('users', $response));
        // Object key must return project
        $this->assertEquals($project->id, $response['project']['id']);
        // Users array must contains nothing
        $this->assertCount(0, $response['users']);
    }

    /** @test */
    public function project_users_getter_can_return_project_with_users()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given users
        $users = factory(User::class, 5)->create();
        // Attach users to project
        $project->users()->attach($users->pluck('id')->toArray());

        // When user make post request with data
        $response = $this->actingAs($user)->post(route('projects::get_project_users'), ['project_id' => $project->id])->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have project key
        $this->assertTrue(array_key_exists('project', $response));
        // This array must have users key
        $this->assertTrue(array_key_exists('users', $response));
        // Object key must return project
        $this->assertEquals($project->id, $response['project']['id']);
        // Users array must contains five users
        $this->assertCount(5, $response['users']);
        $this->assertEquals($users->reverse()->pluck('id')->toArray(), collect($response['users'])->pluck('id')->toArray());
    }

    /** @test */
    public function project_can_have_appointment_brigades()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When we add brigade on project
        $project->brigades()->save($brigade);

        // Then project should have brigades() relation with count 1
        $this->assertCount(1, $project->refresh()->brigades);
        $this->assertEquals($brigade->id, $project->brigades[0]->id);
    }

    /** @test */
    public function brigade_cannot_be_appointed_to_project_by_post_by_user_without_permission()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user without permission
        $brigade = User::whereNotIn('group_id', [5, 6, 8, 13, 14, 19, 23, 27, 31])->inRandomOrder()->first();

        // When user make post request with any data
        $response = $this->actingAs($brigade)->post(route('projects::appoint_brigade', $project->id), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function brigade_cannot_be_appointed_to_project_by_post_by_user_with_permission_but_without_roles()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given brigade with permission
        $brigade = User::whereIn('group_id', [5, 6, 8, 13, 14, 19, 23, 27, 31])->inRandomOrder()->first();

        // When user make post request with any data
        $data = [
            'project_id' => $project->id
        ];
        $response = $this->actingAs($brigade)->post(route('projects::appoint_brigade', $project->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function brigade_can_be_appointed_to_project_by_post_by_user_with_permission_and_time_manager_role()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 14, 19, 23, 27, 31])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given brigade users
        $users = factory(User::class, 5)->create();

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigade->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_brigade', $project->id), $data);

        // Then ...
        // Project should have brigades() relation with count 1
        $this->assertCount(1, $project->refresh()->brigades);
        $this->assertEquals($brigade->id, $project->brigades[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals($response['brigade']['id'], $brigade->id);
    }

    /** @test */
    public function brigade_can_be_appointed_to_project_by_post_by_user_with_permission_and_role_in_project()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 14, 19, 23, 27, 31])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given use role in project
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 8]);
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigade->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_brigade', $project->id), $data);

        // Then ...
        // Project should have brigades() relation with count 1
        $this->assertCount(1, $project->refresh()->brigades);
        $this->assertEquals($brigade->id, $project->brigades[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals($response['brigade']['id'], $brigade->id);
    }

    /** @test */
    public function brigade_project_appointment_function_return_brigade_with_users()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 14, 19, 23, 27, 31])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given use role in project
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 8]);
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given brigade users
        $users = factory(User::class, 3)->create(['brigade_id' => $brigade->id]);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigade->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_brigade', $project->id), $data);

        // Then ...
        // Project should have brigades() relation with count 1
        $this->assertCount(1, $project->refresh()->brigades);
        $this->assertEquals($brigade->id, $project->brigades[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals($response['brigade']['id'], $brigade->id);
        $this->assertEquals(collect($response['brigade']['users'])->pluck('id'), $users->pluck('id'));
    }

    /** @test */
    public function brigade_can_not_be_appointed_to_project_by_post_by_user_with_permission_and_role_in_project_if_brigade_already_was_appointed()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 14, 19, 23, 27, 31])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Appoint brigade to project
        $project->brigades()->save($brigade);

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigade->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_brigade', $project->id), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('name');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('name')[0], 'Бригада уже назначена на этот проект, её нельзя назначить ещё раз');
    }

    /** @test */
    public function project_can_not_have_users()
    {
        // Given project without brigades and users
        $project = factory(Project::class)->create();

        // Then allUsers() function should return empty collection
        $this->assertTrue($project->allUsers()->isEmpty());
    }

    /** @test */
    public function project_can_have_users_cache()
    {
        // Given project without brigades but with users
        $project = factory(Project::class)->create();
        // Given users
        $users = $project->users()->saveMany(factory(User::class, 3)->create());

        // Then allUsers() function should return collection with count 3
        $this->assertCount(3, $project->allUsers());
        // With users
        $this->assertEquals($users->pluck('id'), $project->allUsers()->pluck('id'));
    }

    /** @test */
    public function project_can_have_users_cache_from_brigades()
    {
        // Given project with brigades but without users
        $project = factory(Project::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given brigade users
        $users = factory(User::class, 3)->create(['brigade_id' => $brigade->id]);
        // Appoint brigade to project
        $project->brigades()->save($brigade);

        // Then allUsers() function should return collection with count 3
        $this->assertCount(3, $project->allUsers());
        // With users
        $this->assertEquals($users->pluck('id'), $project->allUsers()->pluck('id'));
    }

    /** @test */
    public function project_can_have_users_cache_from_brigades_and_users()
    {
        // Given project with brigades and users
        $project = factory(Project::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given brigade users
        $users = factory(User::class, 3)->create(['brigade_id' => $brigade->id]);
        // Appoint brigade to project
        $project->brigades()->save($brigade);
        // Given users
        $usersFromProject = $project->users()->saveMany(factory(User::class, 3)->create());

        // Then allUsers() function should return collection with count 6
        $this->assertCount(6, $project->allUsers());
        // With users
        $this->assertEquals($usersFromProject->merge($users)->pluck('id'), $project->allUsers()->pluck('id'));
    }

    /** @test */
    public function project_contracts_started_scope_can_return_nothing()
    {
        // Clear projects
        Project::query()->delete();

        // When we use contractsStarted() scope
        $result = Project::contractsStarted()->get();

        // Then $result should contains nothing
        $this->assertEmpty($result);
    }

    /** @test */
    public function project_contracts_started_scope_can_return_nothing_if_we_dont_have_projects_with_contract_tasks()
    {
        // Given projects
        factory(Project::class, 5)->create();

        // When we use contractsStarted() scope
        $result = Project::contractsStarted()->get();

        // Then $result should contains nothing
        $this->assertEmpty($result);
    }

    /** @test */
    public function project_contracts_started_scope_can_return_projects_with_contract_tasks()
    {
        // Given projects
        factory(Project::class, 5)->create();
        // And one with task
        $project = factory(Project::class)->create();
        $task = factory(Task::class)->create(['status' => 12, 'project_id' => $project->id]);

        // When we use contractsStarted() scope
        $result = Project::contractsStarted()->get();

        // Then $result should contain something
        // We must have one project in result
        $this->assertCount(1, $result);
        // And here we must have $project
        $this->assertEquals([$project->id], $result->pluck('id')->toArray());
    }

    /** @test */
    public function brigade_cannot_be_detached_from_object_by_post_by_user_without_permission()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request with any data
        $response = $this->actingAs($user)->post(route('projects::detach_brigade', $project->id), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function brigade_cannot_be_detached_from_project_by_post_by_user_with_permission_but_without_roles()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request with any data
        $data = [
            'project_id' => $project->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_brigade', $project->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function brigade_cannot_be_detached_from_project_by_post_by_user_with_permission_with_roles_if_brigade_was_not_attached_to_project()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigade->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_brigade', $project->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors('brigade');
    }

    /** @test */
    public function brigade_can_be_detached_from_project_by_post_by_user_with_permission_and_time_manager_role()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $user->id]);
        // Given brigade attachment
        $brigadeForWork = factory(Brigade::class)->create();
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigadeForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_brigade', $project->id), $data);
        // Then ...
        // Project should have brigades() relation with count 1
        $this->assertCount(1, $project->refresh()->brigades);
        $this->assertEquals($brigadeForWork->id, $project->brigades[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals($response['brigade']['id'], $brigadeForWork->id);
        $appointment = $project->appointments[0];

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigadeForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_brigade', $project->id), $data);

        // Then ...
        // Object should have brigades() relation with count 0
        $this->assertCount(0, $project->refresh()->brigades);
        // And appointment should be deleted
        $this->assertCount(0, $project->appointments);
        // With logs
        $this->assertCount(2, $appointment->refresh()->logs);
    }

    /** @test */
    public function brigade_can_be_detached_from_project_by_post_by_user_with_permission_and_role_in_project()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given use role in project
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 8]);
        // Given brigade attachment
        $brigadeForWork = factory(Brigade::class)->create();
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigadeForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_brigade', $project->id), $data);
        // Then ...
        // Project should have brigades() relation with count 1
        $this->assertCount(1, $project->refresh()->brigades);
        $this->assertEquals($brigadeForWork->id, $project->brigades[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals($response['brigade']['id'], $brigadeForWork->id);
        $appointment = $project->appointments[0];

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigadeForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_brigade', $project->id), $data);

        // Then ...
        // Object should have brigades() relation with count 0
        $this->assertCount(0, $project->refresh()->brigades);
        // And appointment should be deleted
        $this->assertCount(0, $project->appointments);
        // With logs
        $this->assertCount(2, $appointment->refresh()->logs);
    }

    /** @test */
    public function when_someone_detach_brigade_with_users_then_brigade_users_should_be_detached_too()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();
        // Given use role in project
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 8]);
        // Given brigade attachment
        $brigadeForWork = factory(Brigade::class)->create();
        // Given some users from brigade
        $users = factory(User::class, 3)->create(['brigade_id' => $brigadeForWork->id]);
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigadeForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::appoint_brigade', $project->id), $data);
        // Then ...
        // Project should have brigades() relation with count 1
        $this->assertCount(1, $project->refresh()->brigades);
        $this->assertEquals($brigadeForWork->id, $project->brigades[0]->id);
        // And appointment should be created
        $this->assertCount(1, $project->appointments);
        // With logs
        $this->assertCount(1, $project->appointments[0]->logs);
        // Some things must be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['brigade']['users'])->pluck('id'), $users->pluck('id'));
        $appointment = $project->appointments[0];

        // When user make post request with data
        $data = [
            'project_id' => $project->id,
            'brigade_id' => $brigadeForWork->id
        ];
        $response = $this->actingAs($user)->post(route('projects::detach_brigade', $project->id), $data);

        // Then ...
        // Object should have brigades() relation with count 0
        $this->assertCount(0, $project->refresh()->brigades);
        // And appointment should be deleted
        $this->assertCount(0, $project->appointments);
        // With logs
        $this->assertCount(2, $appointment->refresh()->logs);
        // And project users should be empty
        $this->assertEquals($project->allUsers(), collect([]));
    }
}

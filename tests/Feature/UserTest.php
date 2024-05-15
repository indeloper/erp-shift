<?php

namespace Tests\Feature;

use App\Models\HumanResources\Brigade;
use App\Models\HumanResources\JobCategory;
use App\Models\HumanResources\ReportGroup;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function user_who_have_birthday_today_scope_must_return_users_who_have_birthday_today()
    {
        User::query()->delete();
        // Given three users with birthdays
        $user1 = User::factory()->create(['birthday' => now()->subYear()->format('d.m.Y')]);
        $user2 = User::factory()->create(['birthday' => now()->subYears(2)->format('d.m.Y')]);
        $user3 = User::factory()->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we use whoHaveBirthdayToday() scope
        $result = User::whoHaveBirthdayToday()->get();

        // Then ...
        // We must have two users in result
        $this->assertCount(2, $result);
        // And here we must have $user1 and $user2
        $this->assertEquals([$user1->id, $user2->id], $result->pluck('id')->toArray());
    }

    /** @test */
    public function user_who_have_birthday_today_scope_can_return_no_one()
    {
        User::query()->delete();
        // Given three users with birthdays
        $user1 = User::factory()->create(['birthday' => now()->subMonth()->subYear()->format('d.m.Y')]);
        $user2 = User::factory()->create(['birthday' => now()->subMonth()->subYears(2)->format('d.m.Y')]);
        $user3 = User::factory()->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we use whoHaveBirthdayToday() scope
        $result = User::whoHaveBirthdayToday()->get();

        // Then $result should contains nothing
        $this->assertEmpty($result);
    }

    /** @test */
    public function user_who_have_birthday_next_week_scope_must_return_users_who_have_birthday_next_week()
    {
        User::query()->delete();
        // Given three users with birthdays
        $user1 = User::factory()->create(['birthday' => now()->subYear()->addWeek()->format('d.m.Y')]);
        $user2 = User::factory()->create(['birthday' => now()->subYears(2)->addWeek()->format('d.m.Y')]);
        $user3 = User::factory()->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we use whoHaveBirthdayNextWeek() scope
        $result = User::whoHaveBirthdayNextWeek()->get();

        // Then ...
        // We must have two users in result
        $this->assertCount(2, $result);
        // And here we must have $user1 and $user2
        $this->assertEquals([$user1->id, $user2->id], $result->pluck('id')->toArray());
    }

    /** @test */
    public function user_who_have_birthday_next_week_scope_can_return_no_one()
    {
        User::query()->delete();
        // Given three users with birthdays
        $user1 = User::factory()->create(['birthday' => now()->subMonth()->subYear()->format('d.m.Y')]);
        $user2 = User::factory()->create(['birthday' => now()->subMonth()->subYears(2)->format('d.m.Y')]);
        $user3 = User::factory()->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we use whoHaveBirthdayNextWeek() scope
        $result = User::whoHaveBirthdayNextWeek()->get();

        // Then $result should contains nothing
        $this->assertEmpty($result);
    }

    /** @test */
    public function user_can_have_job_category()
    {
        // Given user with job category
        $jobCategory = JobCategory::factory()->create();
        $user = User::factory()->create(['job_category_id' => $jobCategory->id]);

        // Then ...
        // User should have jobCategory relation
        $user->refresh();
        $this->assertEquals(JobCategory::class, get_class($user->jobCategory));
        // Especially to provided jobCategory
        $this->assertEquals($user->jobCategory->id, $jobCategory->id);
    }

    /** @test */
    public function user_can_have_report_group_through_job_category()
    {
        // Given report group
        $reportGroup = ReportGroup::factory()->create();
        // Given user with job category
        $jobCategory = JobCategory::factory()->create(['report_group_id' => $reportGroup]);
        $user = User::factory()->create(['job_category_id' => $jobCategory->id]);

        // Then ...
        // User should have reportGroup relation
        $user->refresh();
        $this->assertEquals(ReportGroup::class, get_class($user->reportGroup));
        // Especially to provided reportGroup
        $this->assertEquals($user->reportGroup->id, $reportGroup->id);
    }

    /** @test */
    public function user_without_permission_cannot_change_user_job_category()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When user make post request with any data
        $data = [];
        $response = $this->actingAs($user)->post(route('users::update_job_category'), $data);

        // Then this user must have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_change_user_job_category_without_data()
    {
        // Given user without permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When user make post request with any data
        $data = [];
        $response = $this->actingAs($user)->post(route('users::update_job_category'), $data);

        // Then session should have errors
        $response->assertSessionHasErrors(['user_id', 'job_category_id']);
    }

    /** @test */
    public function user_with_permission_can_change_user_job_category()
    {
        // Given user without permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given user
        $changedUser = User::factory()->create();
        // Given job category
        $jobCategory = JobCategory::factory()->create();

        // When user make post request with any data
        $data = [
            'user_id' => $changedUser->id,
            'job_category_id' => $jobCategory->id,
        ];
        $response = $this->actingAs($user)->post(route('users::update_job_category'), $data);

        // Then ...
        // Everything should be OK
        $response->assertOk();
        // And changed user should have job category
        $changedUser->refresh();
        $this->assertEquals($jobCategory->id, $changedUser->job_category_id);
        // Also changed user should have logs
        $this->assertCount(1, $changedUser->logs);
    }

    /** @test */
    public function user_filter_work_without_any_filters()
    {
        // Given no users
        User::query()->delete();
        // Given user
        $user = User::factory()->create();

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_name()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['first_name' => 'Arthur', 'last_name' => 'Pirozhkov']);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?name={$user->name}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_name_one_more()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['first_name' => 'Lupa', 'last_name' => 'Pupin']);
        $user2 = User::factory()->create(['first_name' => 'Pupa', 'last_name' => 'Lupin']);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index').'?name=pup']))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_name_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['first_name' => 'Tik', 'last_name' => 'Tok']);
        $user2 = User::factory()->create(['first_name' => 'Cat', 'last_name' => 'Concat']);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?name%5B0%5D={$user1->first_name}&name%5B1%5D={$user2->last_name}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);

    }

    /** @test */
    public function user_filter_work_with_email()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create();

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?email={$user->email}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_email_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?email%5B0%5D={$user1->email}&email%5B1%5D={$user2->email}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_department()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['department_id' => 777]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?department_id={$user->department_id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_department_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['department_id' => 555]);
        $user2 = User::factory()->create(['department_id' => 777]);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?department_id%5B0%5D={$user1->department_id}&department_id%5B1%5D={$user2->department_id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_group()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['group_id' => 777]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?group_id={$user->group_id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_group_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['group_id' => 555]);
        $user2 = User::factory()->create(['group_id' => 777]);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?group_id%5B0%5D={$user1->group_id}&group_id%5B1%5D={$user2->group_id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_company()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['company' => 777]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?company={$user->company}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_company_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['company' => 555]);
        $user2 = User::factory()->create(['company' => 777]);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?company%5B0%5D={$user1->company}&company%5B1%5D={$user2->company}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_job_category()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['job_category_id' => 777]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?job_category_id={$user->job_category_id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_job_category_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['job_category_id' => 555]);
        $user2 = User::factory()->create(['job_category_id' => 777]);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?job_category_id%5B0%5D={$user1->job_category_id}&job_category_id%5B1%5D={$user2->job_category_id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_person_phone()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['person_phone' => 98765432101]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index').'?person_phone=765432']))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_person_phone_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['person_phone' => 555]);
        $user2 = User::factory()->create(['person_phone' => 777]);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?person_phone%5B0%5D={$user1->person_phone}&person_phone%5B1%5D={$user2->person_phone}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_work_phone()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['work_phone' => 98765432101]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index').'?work_phone=765432']))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_work_phone_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user1 = User::factory()->create(['work_phone' => 555]);
        $user2 = User::factory()->create(['work_phone' => 777]);

        // When we make post request
        $response = $this->actingAs($user1)->post(route('users::paginated', ['url' => route('users::index')."?work_phone%5B0%5D={$user1->work_phone}&work_phone%5B1%5D={$user2->work_phone}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user1->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_filter_work_with_birthday_from()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['birthday' => now()->subYears(25)->format('d-m-Y')]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?birthday={$user->birthday}|"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_birthday_to()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['birthday' => now()->subYears(10)->format('d-m-Y')]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?birthday=|{$user->birthday}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(1, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(1, $response['data']['users_count']);
        // This one
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function user_filter_work_with_birthday_both_dates()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create(['birthday' => now()->subYears(20)->format('d-m-Y')]);
        $user2 = User::factory()->create(['birthday' => now()->subYears(10)->format('d-m-Y')]);

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?birthday={$user2->birthday}|{$user->birthday}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one user
        $this->assertCount(2, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(2, $response['data']['users_count']);
        // This ones
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
        $this->assertEquals($user2->id, $response['data']['users'][1]['id']);
    }

    /** @test */
    public function user_can_be_appointed_to_project()
    {
        // Given project
        $project = Project::factory()->create();
        // Given user
        $user = User::factory()->create();

        // When we add user on project
        $project->users()->save($user);

        // Then user should have projects() relation with count 1
        $this->assertCount(1, $user->refresh()->appointments);
        $this->assertEquals($project->id, $user->appointments[0]->id);
    }

    /** @test */
    public function user_can_have_many_appoints_to_projects()
    {
        // Given projects
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();
        // Given user
        $user = User::factory()->create();

        // When we add user on projects
        $project1->users()->save($user);
        $project2->users()->save($user);

        // Then user should have projects() relation with count 2
        $this->assertCount(2, $user->refresh()->appointments);
        $this->assertEquals($project1->id, $user->appointments[0]->id);
        $this->assertEquals($project2->id, $user->appointments[1]->id);
    }

    /** @test */
    public function user_filter_work_with_objects()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(5)->create();
        $user = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Attach users to object
        $object->users()->attach($users->pluck('id')->toArray());

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?project_object_id={$object->id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains five users
        $this->assertCount(5, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(5, $response['data']['users_count']);
        // This ones
        $this->assertEquals($users->pluck('id'), collect($response['data']['users'])->pluck('id'));
    }

    /** @test */
    public function user_filter_work_with_objects_array()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(2)->create();
        $users2 = User::factory()->count(2)->create();
        $user = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        $object2 = ProjectObject::factory()->create();
        // Attach users to object
        $object->users()->attach($users->pluck('id')->toArray());
        $object2->users()->attach($users2->pluck('id')->toArray());

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?project_object_id%5B0%5D={$object->id}&project_object_id%5B1%5D={$object2->id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains four users
        $this->assertCount(4, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(4, $response['data']['users_count']);
        // This ones
        $this->assertEquals($users->merge($users2)->pluck('id'), collect($response['data']['users'])->pluck('id'));
    }

    /** @test */
    public function user_filter_work_with_objects_array_and_can_return_nothing()
    {
        // Given no users
        User::query()->delete();
        // Given users
        $users = User::factory()->count(2)->create();
        $users2 = User::factory()->count(2)->create();
        $user = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        $object2 = ProjectObject::factory()->create();

        // When we make post request
        $response = $this->actingAs($user)->post(route('users::paginated', ['url' => route('users::index')."?project_object_id%5B0%5D={$object->id}&project_object_id%5B1%5D={$object2->id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains nothing
        $this->assertCount(0, $response['data']['users']);
        // This array must contains users count
        $this->assertEquals(0, $response['data']['users_count']);
    }

    /** @test */
    public function user_can_have_brigade_relation()
    {
        // Given no users
        User::query()->delete();
        // Given brigade
        $brigade = Brigade::factory()->create();
        // Given user from this brigade
        $user = User::factory()->create(['brigade_id' => $brigade->id]);

        // Then user should have brigade() relation
        $this->assertInstanceOf(Brigade::class, $user->brigade);
        $this->assertEquals($brigade->id, $user->brigade->id);
        $this->assertEquals($brigade->name, $user->brigade_name);
    }

    /** @test */
    public function user_can_have_brigades_relation()
    {
        // Given no users
        User::query()->delete();
        // Given user
        $user = User::factory()->create();
        // Given brigades where user is foreman
        $brigade = Brigade::factory()->create(['foreman_id' => $user->id]);

        // Then user should have brigades() relation
        $this->assertEquals($brigade->id, $user->brigades->first()->id);
    }
}

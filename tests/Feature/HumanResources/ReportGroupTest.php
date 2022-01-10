<?php

namespace Tests\Feature\HumanResources;

use App\Models\HumanResources\{JobCategory, ReportGroup};
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportGroupTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function report_group_getter_without_parameters_returns_active_report_groups()
    {
        ReportGroup::query()->delete();
        // Given user
        $user = factory(User::class)->create();
        // Given two active report groups and one deleted
        $reportGroup1 = factory(ReportGroup::class)->create();
        $reportGroup2 = factory(ReportGroup::class)->create(['deleted_at' => now()]);
        $reportGroup3 = factory(ReportGroup::class)->create();

        // When user make get request
        $response = $this->actingAs($user)->get(route('human_resources.report_groups.get'))->json();

        // Then ...
        // Response must have two report groups
        $this->assertCount(2, $response);
        // First and third report groups
        $this->assertEquals([$reportGroup1->id, $reportGroup3->id], [$response[0]['id'], $response[1]['id']]);
    }

    /** @test */
    public function report_group_getter_with_query_parameter_returns_only_matched_report_groups()
    {
        // Given user
        $user = factory(User::class)->create();
        // Given two active report groups and one deleted
        $reportGroup1 = factory(ReportGroup::class)->create();
        $reportGroup2 = factory(ReportGroup::class)->create(['deleted_at' => now()]);
        $reportGroup3 = factory(ReportGroup::class)->create();

        // When user make get request
        $response = $this->actingAs($user)->get(route('human_resources.report_groups.get',"q={$reportGroup1->name}"))->json();

        // Then ...
        // Response must have one report group
        $this->assertCount(1, $response);
        // First report group
        $this->assertEquals($reportGroup1->id, $response[0]['id']);
    }

    /** @test */
    public function user_without_permission_cannot_create_report_group()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6])->inRandomOrder()->first();

        // When user make post request
        $response = $this->actingAs($user)->post(route('human_resources.report_group.store'), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_create_report_group_without_data()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();

        // When user make post request without data
        $response = $this->actingAs($user)->post(route('human_resources.report_group.store'), []);

        // Then user should have errors
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function user_with_permission_can_create_report_group_with_name_only()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();

        // When user make post request with data
        $data = ['name' => 'something'];
        $response = $this->actingAs($user)->post(route('human_resources.report_group.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = ReportGroup::get()->last();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have one log
        $this->assertCount(1, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_can_create_report_group_with_name_and_free_job_categories()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given some job categories
        $jobCategories = factory(JobCategory::class, 3)->create();

        // When user make post request with data
        $data = [
            'name' => 'something',
            'job_categories' => $jobCategories->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->post(route('human_resources.report_group.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = ReportGroup::get()->last();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have job categories relation with count 3
        $this->assertCount(3, $createdRow->jobCategories);
        // And job category should have logs relation with count 2
        $this->assertCount(2, $createdRow->jobCategories[0]->logs);
        // And model should have one log
        $this->assertCount(1, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_cannot_create_report_group_with_name_and_not_free_job_categories()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given some job categories with report groups
        $jobCategories = factory(JobCategory::class, 3)->create(['report_group_id' => 1]);

        // When user make post request with data
        $data = [
            'name' => 'something',
            'job_categories' => $jobCategories->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->post(route('human_resources.report_group.store'), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('override');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('override')[0], json_encode($jobCategories->pluck('id')->toArray()));
    }

    /** @test */
    public function user_with_permission_cannot_create_report_group_with_name_and_not_free_job_categories_but_can_after_approval()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given some job categories with report groups
        $jobCategories = factory(JobCategory::class, 3)->create(['report_group_id' => 1]);

        // When user make post request with data
        $data = [
            'name' => 'something',
            'job_categories' => $jobCategories->pluck('id')->toArray(),
            'skip_job_categories_check' => 1
        ];
        $response = $this->actingAs($user)->post(route('human_resources.report_group.store'), $data);

        // We must see new example in database
        $createdRow = ReportGroup::get()->last();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have job categories relation with count 3
        $this->assertCount(3, $createdRow->jobCategories);
        // And job category should have logs relation with count 2
        $this->assertCount(2, $createdRow->jobCategories[0]->logs);
        // And model should have one log
        $this->assertCount(1, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_cannot_create_report_group_with_name_that_already_exist()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create(['name' => 'ALREADY EXIST']);

        // When user make post request with data
        $data = [
            'name' => $reportGroup->name,
        ];
        $response = $this->actingAs($user)->post(route('human_resources.report_group.store'), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function user_without_permission_cannot_see_report_group_index()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6])->inRandomOrder()->first();

        // When user make get request
        $response = $this->actingAs($user)->get(route('human_resources.report_group.index'));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function report_group_can_have_users_relation()
    {
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create(['report_group_id' => $reportGroup->id]);
        // Given user
        $user = factory(User::class)->create(['job_category_id' => $jobCategory->id]);

        // Then report group should have users relation
        $this->assertCount(1, $reportGroup->users);
        $this->assertEquals($user->id, $reportGroup->users[0]->id);
    }

    /** @test */
    public function report_group_filter_work_without_any_filters_and_report_groups()
    {
        // Given no report groups
        // Given user
        $user = factory(User::class)->create();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.report_group.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains nothing
        $this->assertCount(0, $response['data']['report_groups']);
    }

    /** @test */
    public function report_group_filter_work_without_any_filters()
    {
        // Given three report groups
        factory(ReportGroup::class, 3)->create();
        // Given user
        $user = factory(User::class)->create();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.report_group.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains three report groups
        $this->assertCount(3, $response['data']['report_groups']);
        // And report groups should have users count
        $this->assertArrayHasKey('job_categories_count', $response['data']['report_groups'][0]);
    }

    /** @test */
    public function report_group_filter_by_name()
    {
        // Given three report groups
        factory(ReportGroup::class, 2)->create();
        $repotGroup = factory(ReportGroup::class)->create();
        // Given user
        $user = factory(User::class)->create();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.report_group.paginated', ['url' => route('human_resources.report_group.index') . "?name={$repotGroup->name}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one job category
        $this->assertCount(1, $response['data']['report_groups']);
        // Especial one
        $this->assertEquals($response['data']['report_groups'][0]['id'], $repotGroup->id);
        // And categories should have users count
        $this->assertArrayHasKey('job_categories_count', $response['data']['report_groups'][0]);
    }

    /** @test */
    public function user_without_permission_cannot_see_report_group_show()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();

        // When user make get request
        $response = $this->actingAs($user)->get(route('human_resources.report_group.show', $reportGroup->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_without_permission_cannot_destroy_report_group()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();

        // When user make delete request
        $response = $this->actingAs($user)->delete(route('human_resources.report_group.destroy', $reportGroup->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_destroy_report_group()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group without job categories
        $reportGroup = factory(ReportGroup::class)->create();

        // When user make delete request
        $response = $this->actingAs($user)->delete(route('human_resources.report_group.destroy', $reportGroup->id));

        /// Then ...
        // Report group should be deleted
        $reportGroup->refresh();
        $this->assertSoftDeleted($reportGroup);
        // And report group should have two logs
        $this->assertCount(2, $reportGroup->logs);
        // Some response need to be send
        $this->assertEquals('true', $response->getContent());
    }

    /** @test */
    public function user_with_permission_can_destroy_report_group_with_job_categories()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();
        // Given job categories
        $jobCategories = factory(JobCategory::class, 5)->create(['report_group_id' => $reportGroup->id]);

        // When user make delete request
        $response = $this->actingAs($user)->delete(route('human_resources.report_group.destroy', $reportGroup->id));

        /// Then ...
        // Report group should be deleted
        $reportGroup->refresh();
        $this->assertSoftDeleted($reportGroup);
        // And report group should have two logs
        $this->assertCount(2, $reportGroup->logs);
        // Job categories should loose their report_group_id
        $this->assertEquals(null, $jobCategories[0]->refresh()->report_group_id);
        // Job categories should have some logs
        $this->assertCount(2, $jobCategories[0]->logs);
        // Some response need to be send
        $this->assertEquals('true', $response->getContent());
    }

    /** @test */
    public function user_without_permission_cannot_update_report_group()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();

        // When user make put request
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_update_report_group_with_already_existed_name()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();
        // Given one more report group
        $additionalReportGroup = factory(ReportGroup::class)->create();

        // When user make put request with data
        $data = [
            'name' => $additionalReportGroup->name,
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then session should have error
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function user_with_permission_can_update_report_group_to_same_name()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create(['name' => 'some name']);

        // When user make put request with data
        $data = [
            'report_group_id' => $reportGroup->id,
            'name' => $reportGroup->name,
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then session should not have error
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function user_with_permission_can_update_report_group_name()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();

        // When user make put request with data
        $data = [
            'report_group_id' => $reportGroup->id,
            'name' => 'new name',
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then ...
        // We must see updated row in database
        $createdRow = $reportGroup->refresh();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have two logs
        $this->assertCount(2, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_can_update_report_group_by_adding_job_categories()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();
        // Given job categories
        $jobCategories = factory(JobCategory::class, 3)->create();

        // When user make put request with data
        $data = [
            'report_group_id' => $reportGroup->id,
            'name' => 'new name',
            'job_categories' => $jobCategories->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then ...
        // We must see new example in database
        $createdRow = $reportGroup->refresh();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have job categories relation with count 3
        $this->assertCount(3, $createdRow->jobCategories);
        // And job category should have logs relation with count 2
        $this->assertCount(2, $createdRow->jobCategories[0]->logs);
        // And model should have two logs
        $this->assertCount(2, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_can_update_report_group_by_removing_job_categories()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group with job categories
        $reportGroup = factory(ReportGroup::class)->create();
        // Given job categories
        $jobCategories = factory(JobCategory::class, 3)->create(['report_group_id' => $reportGroup->id]);

        // When user make put request with data
        $data = [
            'report_group_id' => $reportGroup->id,
            'name' => 'new name',
            'deleted_job_categories' => $jobCategories->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then ...
        // We must see new example in database
        $createdRow = $reportGroup->refresh();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should lost job categories relation
        $this->assertCount(0, $createdRow->jobCategories);
        // And job category should have logs relation with count 2
        $this->assertCount(2, $jobCategories[0]->refresh()->logs);
        // And model should have two logs
        $this->assertCount(2, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_can_update_report_group_by_adding_and_removing_job_categories()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group with job categories
        $reportGroup = factory(ReportGroup::class)->create();
        // Given job categories
        $jobCategories = factory(JobCategory::class, 3)->create(['report_group_id' => $reportGroup->id]);
        $newCategories = factory(JobCategory::class, 2)->create();

        // When user make put request with data
        $data = [
            'report_group_id' => $reportGroup->id,
            'name' => 'new name',
            'deleted_job_categories' => $jobCategories->pluck('id')->toArray(),
            'job_categories' => $newCategories->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then ...
        // We must see new example in database
        $createdRow = $reportGroup->refresh();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have job categories relation with count 2
        $this->assertCount(2, $createdRow->jobCategories);
        // And job category should have logs relation with count 2
        $this->assertCount(2, $jobCategories[0]->refresh()->logs);
        $this->assertCount(2, $newCategories[0]->refresh()->logs);
        // And model should have two logs
        $this->assertCount(2, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_cannot_update_report_group_by_adding_job_categories_with_report_group()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group with job categories
        $reportGroup = factory(ReportGroup::class)->create();
        // Given job categories
        $jobCategories = factory(JobCategory::class, 3)->create(['report_group_id' => 1]);

        // When user make put request with data
        $data = [
            'report_group_id' => $reportGroup->id,
            'name' => 'new name',
            'job_categories' => $jobCategories->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('override');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('override')[0], json_encode($jobCategories->pluck('id')->toArray()));
    }

    /** @test */
    public function user_with_permission_cannot_update_report_group_by_adding_job_categories_with_report_group_but_can_after_approve()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6])->inRandomOrder()->first();
        // Given report group with job categories
        $reportGroup = factory(ReportGroup::class)->create();
        // Given job categories
        $jobCategories = factory(JobCategory::class, 3)->create(['report_group_id' => 1]);

        // When user make put request with data
        $data = [
            'report_group_id' => $reportGroup->id,
            'name' => 'new name',
            'job_categories' => $jobCategories->pluck('id')->toArray(),
            'skip_job_categories_check' => 1
        ];
        $response = $this->actingAs($user)->put(route('human_resources.report_group.update', $reportGroup->id), $data);

        // Then ...
        // We must see new example in database
        $createdRow = $reportGroup->refresh();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have job categories relation with count 3
        $this->assertCount(3, $createdRow->jobCategories);
        // And job category should have logs relation with count 2
        $this->assertCount(2, $jobCategories[0]->refresh()->logs);
        // And model should have two logs
        $this->assertCount(2, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.report_group.show', $createdRow->id));
    }
}

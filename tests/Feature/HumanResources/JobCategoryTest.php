<?php

namespace Tests\Feature\HumanResources;

use App\Models\HumanResources\JobCategory;
use App\Models\HumanResources\JobCategoryTariff;
use App\Models\HumanResources\ReportGroup;
use App\Models\HumanResources\TariffRates;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class JobCategoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function factory_can_create_a_model()
    {
        // When we use factory
        $jobCategory = factory(JobCategory::class)->create();

        // Then ...
        // Model instance should have proper class
        $this->assertEquals(JobCategory::class, get_class($jobCategory));
        // And model should have name
        $this->assertNotEmpty($jobCategory->name);
        // And model should have author
        $this->assertNotEmpty($jobCategory->user_id);
        // And model should have one log
        $this->assertCount(1, $jobCategory->logs);
    }

    /** @test */
    public function job_category_have_author()
    {
        // When we use factory
        $user = factory(User::class)->create();
        $jobCategory = factory(JobCategory::class)->create(['user_id' => $user->id]);

        // Then ...
        // Model instance should have author relation
        $author = $jobCategory->author;
        $this->assertEquals(User::class, get_class($author));
        // And author should be created one
        $this->assertEquals($user->id, $author->id);
        // And model should have one log
        $this->assertCount(1, $jobCategory->logs);
    }

    /** @test */
    public function job_category_can_have_tariffs()
    {
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();

        // When we store tariff in category
        $tariff = factory(JobCategoryTariff::class)->create();
        $jobCategory->tariffs()->save($tariff);
        $jobCategory->refresh();

        // Then ...
        // Job category must have tariffs relation with one tariff
        $this->assertCount(1, $jobCategory->tariffs);
        // And this tariff should be created one
        $jobCategoryTariff = $jobCategory->tariffs->first();
        $this->assertEquals([$jobCategoryTariff->tariff_id, $jobCategoryTariff->rate], [$tariff->tariff_id, $tariff->rate]);
        // And tariff should have one log
        $this->assertCount(2, $tariff->refresh()->logs);
        // And job category should have one log
        $this->assertCount(1, $jobCategory->logs);
    }

    /** @test */
    public function job_category_can_have_report_group()
    {
        // Given job category with report group
        $reportGroup = factory(ReportGroup::class)->create();
        $jobCategory = factory(JobCategory::class)->create(['report_group_id' => $reportGroup->id]);

        // Then ...
        // Job category must have report group relation
        $this->assertEquals(ReportGroup::class, get_class($jobCategory->reportGroup));
        // And this report group should be created one
        $jobCategoryReportGroup = $jobCategory->reportGroup;
        $this->assertEquals($jobCategoryReportGroup->name, $reportGroup->name);
        // And model should have one log
        $this->assertCount(1, $jobCategory->logs);
    }

    /** @test */
    public function job_category_can_be_created_by_post_only_by_users_with_permission()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request with any data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.job_category.store'), $data);

        // Then this user must have 403
        $response->assertForbidden();
    }

    /** @test */
    public function job_category_cannot_be_created_by_post_if_no_data_is_provided()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request without data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.job_category.store'), $data);

        // Then ...
        // In session should be some errors
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function job_category_cannot_be_created_by_post_if_provided_name_already_exists()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        factory(JobCategory::class)->create(['name' => 'testing']);

        // When user make post request without data
        $data = ['name' => 'testing'];
        $response = $this->actingAs($user)->post(route('human_resources.job_category.store'), $data);

        // Then session should have errors
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function job_category_can_be_created_by_post_if_only_name_provided()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request without data
        $data = ['name' => 'testing'];
        $response = $this->actingAs($user)->post(route('human_resources.job_category.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = JobCategory::get()->last();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        // And model should have one log
        $this->assertCount(1, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.job_category.show', $createdRow->id));
    }

    /** @test */
    public function job_category_can_be_created_by_post_with_tariffs()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make post request without data
        $data = [
            'name' => 'testing',
            'tariffs' => [
                [
                    'tariff_id' => TariffRates::inRandomOrder()->first()->id,
                    'rate' => $this->faker->numberBetween(0, 1000),
                ],
                [
                    'tariff_id' => TariffRates::inRandomOrder()->first()->id,
                    'rate' => $this->faker->numberBetween(0, 1000),
                ],
                [
                    'tariff_id' => TariffRates::inRandomOrder()->first()->id,
                    'rate' => $this->faker->numberBetween(0, 1000),
                ]
            ]
        ];
        $response = $this->actingAs($user)->post(route('human_resources.job_category.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = JobCategory::get()->last();
        $this->assertEquals(['name' => $data['name'], 'user_id' => $user->id], [
            'name' => $createdRow->name,
            'user_id' => $createdRow->user_id,
        ]);
        $tariffs = $createdRow->tariffs;
        // And new row must have tariffs relation with count 3
        $this->assertCount(3, $tariffs);
        // And all tariffs should have one log
        $this->assertCount(1, $tariffs[0]->logs);
        $this->assertCount(1, $tariffs[1]->logs);
        $this->assertCount(1, $tariffs[2]->logs);
        // And model should have one log
        $this->assertCount(1, $createdRow->logs);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.job_category.show', $createdRow->id));
    }

    /** @test */
    public function job_category_can_have_users_relation()
    {
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();
        // And two users with this job category, one without any job category
        $user1 = factory(User::class)->create(['job_category_id' => $jobCategory->id]);
        $user2 = factory(User::class)->create();
        $user3 = factory(User::class)->create(['job_category_id' => $jobCategory->id]);

        // Then ...
        // Job category should have users relation
        $jobCategory->refresh();
        $this->assertNotEmpty($jobCategory->users);
        // And this relation should have count equals 2
        $this->assertCount(2, $jobCategory->users);
        $this->assertEquals([$user1->id, $user3->id], $jobCategory->users->pluck('id')->toArray());
    }

    /** @test */
    public function job_category_getter_without_parameters_returns_active_job_categories()
    {
        JobCategory::query()->delete();
        // Given user
        $user = factory(User::class)->create();
        // Given two active job categories and one deleted
        $jobCategory1 = factory(JobCategory::class)->create();
        $jobCategory2 = factory(JobCategory::class)->create(['deleted_at' => now()]);
        $jobCategory3 = factory(JobCategory::class)->create();

        // When user make get request
        $response = $this->actingAs($user)->get(route('human_resources.job_category.get'))->json();

        // Then ...
        // Response must have two job categories
        $this->assertCount(2, $response);
        // First and third job category
        $this->assertEquals([$jobCategory1->id, $jobCategory3->id], [$response[0]['id'], $response[1]['id']]);
    }

    /** @test */
    public function job_category_getter_with_query_parameter_returns_only_matched_job_categories()
    {
        // Given user
        $user = factory(User::class)->create();
        // Given two active job categories and one deleted
        $jobCategory1 = factory(JobCategory::class)->create();
        $jobCategory2 = factory(JobCategory::class)->create(['deleted_at' => now()]);
        $jobCategory3 = factory(JobCategory::class)->create();

        // When user make get request
        $response = $this->actingAs($user)->get(route('human_resources.job_category.get',"q={$jobCategory1->name}"))->json();

        // Then ...
        // Response must have one job category
        $this->assertCount(1, $response);
        // First job category
        $this->assertEquals($jobCategory1->id, $response[0]['id']);
    }

    /** @test */
    public function job_category_cannot_be_destroyed_by_user_without_permission()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();

        // When user make delete request
        $response = $this->actingAs($user)->delete(route('human_resources.job_category.destroy', $jobCategory->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function when_we_destroy_job_category_some_things_should_happen()
    {
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();
        // Given user with permission
        $actor = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given users related to job category
        $user1 = factory(User::class)->create(['job_category_id' => $jobCategory->id]);
        $user2 = factory(User::class)->create(['job_category_id' => $jobCategory->id]);
        $user3 = factory(User::class)->create(['job_category_id' => $jobCategory->id]);

        // When actor make delete request
        $response = $this->actingAs($actor)->delete(route('human_resources.job_category.destroy', $jobCategory->id));

        // Then ...
        // Job category should be deleted
        $jobCategory->refresh();
        $this->assertSoftDeleted($jobCategory);
        // And job category should have two logs
        $this->assertCount(2, $jobCategory->logs);
        // And users should loose their job category
        $this->assertEquals([null, null, null], [$user1->refresh()->job_category_id, $user2->refresh()->job_category_id, $user3->refresh()->job_category_id]);
        // And users should have log about it
        $this->assertCount(1, $user1->logs);
        // Some response need to be send
        $this->assertEquals('true', $response->getContent());
    }

    /** @test */
    public function users_without_permission_cannot_see_job_categories_index_page()
    {
        // Given user without permission
        $actor = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When user make get request
        $response = $this->actingAs($actor)->get(route('human_resources.job_category.index'));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function job_category_filter_work_without_any_filters_and_job_categories()
    {
        // Given no job categories
        // Given user
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.job_category.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains nothing
        $this->assertCount(0, $response['data']['job_categories']);
    }

    /** @test */
    public function job_category_filter_work_without_any_filters()
    {
        // Given three job categories
        factory(JobCategory::class, 3)->create();
        // Given user
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.job_category.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains three job categories
        $this->assertCount(3, $response['data']['job_categories']);
        // And categories should have users count
        $this->assertArrayHasKey('users_count', $response['data']['job_categories'][0]);
    }

    /** @test */
    public function job_category_filter_by_name()
    {
        // Given three job categories
        factory(JobCategory::class, 2)->create();
        $jobCategory = factory(JobCategory::class)->create();
        // Given user
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.job_category.paginated', ['url' => route('human_resources.job_category.index') . "?name={$jobCategory->name}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one job category
        $this->assertCount(1, $response['data']['job_categories']);
        // Especial one
        $this->assertEquals($response['data']['job_categories'][0]['id'], $jobCategory->id);
        // And categories should have users count
        $this->assertArrayHasKey('users_count', $response['data']['job_categories'][0]);
    }

    /** @test */
    /* NO ARRAY SEARCH ALLOWED HERE
    public function job_category_filter_by_names()
    {
        // Given three job categories
        factory(JobCategory::class)->create();
        $jobCategory1 = factory(JobCategory::class)->create();
        $jobCategory2 = factory(JobCategory::class)->create();
        // Given user
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.job_category.paginated', ['url' => route('human_resources.job_category.index') . "?name%5B0%5D={$jobCategory1->name}&name%5B1%5D={$jobCategory2->name}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains two job categories
        $this->assertCount(2, $response['data']['job_categories']);
        // Job category 1 and 2
        $this->assertEquals([$response['data']['job_categories'][0]['id'], $response['data']['job_categories'][1]['id']], [$jobCategory1->id, $jobCategory2->id]);
        // And categories should have users count
        $this->assertArrayHasKey('users_count', $response['data']['job_categories'][0]);
    }*/

    /** @test */
    public function job_category_filter_by_report_group()
    {
        // Given report group
        $reportGroup = factory(ReportGroup::class)->create();
        // Given three job categories
        factory(JobCategory::class, 2)->create();
        $jobCategory = factory(JobCategory::class)->create(['report_group_id' => $reportGroup->id]);
        // Given user
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.job_category.paginated', ['url' => route('human_resources.job_category.index') . "?report_group_id={$reportGroup->id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one job category
        $this->assertCount(1, $response['data']['job_categories']);
        // Especial one
        $this->assertEquals($response['data']['job_categories'][0]['id'], $jobCategory->id);
        // And categories should have users count
        $this->assertArrayHasKey('users_count', $response['data']['job_categories'][0]);
    }

    /** @test */
    public function job_category_filter_by_report_groups()
    {
        // Given report groups
        $reportGroup1 = factory(ReportGroup::class)->create();
        $reportGroup2 = factory(ReportGroup::class)->create();
        // Given three job categories
        factory(JobCategory::class)->create();
        $jobCategory1 = factory(JobCategory::class)->create(['report_group_id' => $reportGroup1->id]);
        $jobCategory2 = factory(JobCategory::class)->create(['report_group_id' => $reportGroup2->id]);
        // Given user
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(
            route('human_resources.job_category.paginated', ['url' => route('human_resources.job_category.index') . "?report_group_id%5B0%5D={$reportGroup1->id}&report_group_id%5B1%5D={$reportGroup2->id}"])
        )->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains two job categories
        $this->assertCount(2, $response['data']['job_categories']);
        // Job category 1 and 2
        $this->assertEquals([$response['data']['job_categories'][0]['id'], $response['data']['job_categories'][1]['id']], [$jobCategory1->id, $jobCategory2->id]);
        // And categories should have users count
        $this->assertArrayHasKey('users_count', $response['data']['job_categories'][0]);
    }

    /** @test */
    public function job_category_filter_by_report_group_and_name()
    {
        // Given report groups
        $reportGroup1 = factory(ReportGroup::class)->create();
        $reportGroup2 = factory(ReportGroup::class)->create();
        // Given three job categories
        factory(JobCategory::class)->create();
        $jobCategory1 = factory(JobCategory::class)->create(['report_group_id' => $reportGroup1->id]);
        $jobCategory2 = factory(JobCategory::class)->create(['report_group_id' => $reportGroup2->id]);
        // Given user
        $user = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(
            route('human_resources.job_category.paginated', ['url' => route('human_resources.job_category.index') . "?report_group_id={$reportGroup1->id}&name={$jobCategory1->name}"])
        )->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains two job categories
        $this->assertCount(1, $response['data']['job_categories']);
        // Job category 1 and 2
        $this->assertEquals($response['data']['job_categories'][0]['id'], $jobCategory1->id);
        // And categories should have users count
        $this->assertArrayHasKey('users_count', $response['data']['job_categories'][0]);
    }

    /** @test */
    public function users_without_permission_cannot_see_job_category_show_page()
    {
        // Given user without permission
        $actor = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();

        // When user make get request
        $response = $this->actingAs($actor)->get(route('human_resources.job_category.show', $jobCategory->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function users_without_permission_cannot_update_job_category()
    {
        // Given user without permission
        $actor = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();

        // When user make put request
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function users_with_permission_cannot_update_job_category_if_provided_name_already_exists()
    {
        // Given user with permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given two job categories
        $jobCategory = factory(JobCategory::class)->create();
        factory(JobCategory::class)->create(['name' => 'something new']);

        // When user make put request with data
        $data = [
            'name' => 'something new'
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id), $data);

        // Then session should have errors
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function users_with_permission_can_update_job_category_name()
    {
        // Given user wit permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();

        // When user make put request with data
        $data = [
            'name' => 'something new'
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id), $data);

        // And refresh our category
        $jobCategory->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And our jobCategory must change
        $this->assertEquals($data['name'], $jobCategory->name);
        // And job category should have two logs - about creating and updating
        $this->assertCount(2, $jobCategory->logs);
    }

    /** @test */
    public function users_with_permission_can_update_job_category_tariff_rate()
    {
        // Given user wit permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();
        // Given tariff
        $tariff = factory(JobCategoryTariff::class)->create(['rate' => 78]);
        $jobCategory->tariffs()->save($tariff);

        // When we make put request with data
        $data = [
            'job_category' => $jobCategory->id,
            'name' => $jobCategory->name,
            'tariffs' => [
                [
                    'id' => $tariff->id,
                    'tariff_id' => $tariff->tariff_id,
                    'rate' => 5
                ]
            ]
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id), $data);
        // And refresh job category
        $jobCategory->refresh();
        // And refresh tariff
        $tariff->refresh();

        // Then tariff must change
        $this->assertEquals($data['tariffs'][0], [
            'id' => $tariff->id,
            'tariff_id' => $tariff->tariff_id,
            'rate' => $tariff->rate,
        ]);
        // And tariff must have logs relation
        $this->assertCount(3, $tariff->logs);
    }

    /** @test */
    public function users_with_permission_can_update_job_category_tariff_tariff_id()
    {
        // Given user wit permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();
        // Given tariffs
        $tariff = factory(JobCategoryTariff::class)->create(['rate' => 78, 'tariff_id' => 1]);
        $tariff2 = factory(JobCategoryTariff::class)->create(['tariff_id' => 2]);
        $jobCategory->tariffs()->save($tariff);

        // When we make put request with data
        $data = [
            'job_category' => $jobCategory->id,
            'name' => $jobCategory->name,
            'tariffs' => [
                [
                    'id' => $tariff->id,
                    'tariff_id' => $tariff2->tariff_id,
                    'rate' => 50
                ]
            ]
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id), $data);
        // And refresh job category
        $jobCategory->refresh();
        // And refresh tariff
        $tariff->refresh();

        // Then tariff must change
        $this->assertEquals($data['tariffs'][0], [
            'id' => $tariff->id,
            'tariff_id' => $tariff->tariff_id,
            'rate' => $tariff->rate,
        ]);
        // And tariff must have logs relation
        $this->assertCount(3, $tariff->logs);
    }

    /** @test */
    public function users_with_permission_can_delete_job_category_tariff()
    {
        // Given user wit permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();
        // Given tariff
        $tariff = factory(JobCategoryTariff::class)->create();
        $jobCategory->tariffs()->save($tariff);

        // When we make put request with data
        $data = [
            'job_category' => $jobCategory->id,
            'name' => $jobCategory->name,
            'deleted_tariffs' => [$tariff->id]
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id), $data);
        // And refresh job category
        $jobCategory->refresh();

        // Then tariff must be deleted
        $tariff->refresh();
        $this->assertSoftDeleted($tariff);
        // And tariffs relation of job category must be empty
        $this->assertCount(0, $jobCategory->tariffs);
        // And tariff must have logs relation
        $this->assertCount(3, $tariff->logs);
    }

    /** @test */
    public function users_with_permission_can_create_job_category_tariff_by_job_category_update()
    {
        // Given user wit permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create(['name' => 'testing']);
        // Given some tariff
        $tariff_id = TariffRates::inRandomOrder()->first()->id;
        // When we make put request with data
        $data = [
            'job_category' => $jobCategory->id,
            'name' => $jobCategory->name,
            'tariffs' => [
                [
                    'tariff_id' => $tariff_id,
                    'rate' => 50
                ]
            ]
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id), $data);
        // And refresh job category
        $jobCategory->refresh();

        // Then ...
        // Tariffs relation of job category must grow
        $this->assertCount(1, $jobCategory->tariffs);
        // And tariff must have logs relation
        $this->assertCount(1, $jobCategory->tariffs()->first()->logs);
    }

    /** @test */
    public function users_with_permission_can_update_job_category_mixed()
    {
        /**
         * User will update two old tariffs, delete one and create one more
         */
        // Given user wit permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create(['name' => 'testing']);
        // Given some tariffs
        $jobCategory->tariffs()->saveMany(factory(JobCategoryTariff::class, 3)->create());
        $tariff1 = $jobCategory->tariffs[0];
        $tariff2 = $jobCategory->tariffs[1];
        $tariff3 = $jobCategory->tariffs[2];
        // When we make put request with data
        $data = [
            'job_category' => $jobCategory->id,
            'name' => $jobCategory->name,
            'deleted_tariffs' => [$tariff3->id],
            'tariffs' => [
                [
                    'id' => $tariff1->id,
                    'tariff_id' => $tariff1->tariff_id,
                    'rate' => 50
                ],
                [
                    'id' => $tariff2->id,
                    'tariff_id' => $tariff2->tariff_id,
                    'rate' => 100
                ],
                [
                    'tariff_id' => $tariff2->tariff_id,
                    'rate' => 1000
                ]
            ]
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update', $jobCategory->id), $data);
        // And refresh job category
        $jobCategory->refresh();

        // Then ...
        // Tariffs relation of job category must grow
        $this->assertCount(3, $jobCategory->tariffs);
        // Two tariffs must update
        $tariff1->refresh();
        $this->assertEquals($data['tariffs'][0], [
            'id' => $tariff1->id,
            'tariff_id' => $tariff1->tariff_id,
            'rate' => $tariff1->rate,
        ]);
        $tariff2->refresh();
        $this->assertEquals($data['tariffs'][1], [
            'id' => $tariff2->id,
            'tariff_id' => $tariff2->tariff_id,
            'rate' => $tariff2->rate,
        ]);
        // One tariff must be new
        $this->assertNotEquals($tariff3->id, $jobCategory->tariffs[2]->id);
        // One must be deleted
        $tariff3->refresh();
        $this->assertSoftDeleted($tariff3);
        // And tariff must have logs relation
        $this->assertCount(3, $tariff1->logs);
        $this->assertCount(3, $tariff2->logs);
        $this->assertCount(3, $tariff3->logs);
        $this->assertCount(1, $jobCategory->tariffs[2]->logs);
    }

    /** @test */
    public function users_without_permission_cannot_update_job_category_users()
    {
        // Given user without permission
        $actor = User::whereNotIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();

        // When user make put request
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update_users', $jobCategory->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function users_with_permission_can_update_job_category_users_by_adding_them()
    {
        // Given user with permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category
        $jobCategory = factory(JobCategory::class)->create();

        // When user make put request with data
        $usersCollection = factory(User::class, 5)->create();
        $data = [
            'user_ids' => $usersCollection->pluck('id')->toArray()
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update_users', $jobCategory->id), $data);

        // Then job category users relation should have length equal to 5
        $jobCategory->refresh();
        $this->assertCount(5, $jobCategory->users);
        $this->assertEquals($data['user_ids'], $jobCategory->users->pluck('id')->toArray());
        // And users should have logs
        $this->assertCount(1, $jobCategory->users[0]->logs);
    }

    /** @test */
    public function users_with_permission_can_update_job_category_users_by_detaching_them()
    {
        // Given user with permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category with users
        $jobCategory = factory(JobCategory::class)->create();
        $jobCategory->users()->saveMany($usersCollection = factory(User::class, 5)->create());
        $detachUsers = $usersCollection->splice(0, 2);

        // When user make put request with data
        $data = [
            'deleted_user_ids' => $detachUsers->pluck('id')->toArray()
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update_users', $jobCategory->id), $data);

        // Then job category users relation should have length equal to 3
        $jobCategory->refresh();
        $this->assertCount(3, $jobCategory->users);
        $this->assertEquals($usersCollection->pluck('id')->toArray(), $jobCategory->users->pluck('id')->toArray());
        // And detached users should loose their job category id
        $this->assertEquals(null, $detachUsers[0]->refresh()->job_category_id);
        $this->assertEquals(null, $detachUsers[1]->refresh()->job_category_id);
        // And detached users should have logs
        $this->assertCount(2, $detachUsers[0]->refresh()->logs);
    }

    /** @test */
    public function users_with_permission_can_update_job_category_users_by_adding_and_detaching_them()
    {
        // Given user with permission
        $actor = User::whereIn('group_id', [5, 6, 8, 13, 19, 27])->inRandomOrder()->first();
        // Given job category with users
        $jobCategory = factory(JobCategory::class)->create();
        $jobCategory->users()->saveMany($usersCollection = factory(User::class, 5)->create());
        $additionalUsers = factory(User::class, 2)->create();
        $detachUsers = $usersCollection->splice(0, 2);

        // When user make put request with data
        $data = [
            'deleted_user_ids' => $detachUsers->pluck('id')->toArray(),
            'user_ids' => $additionalUsers->pluck('id')->toArray()
        ];
        $response = $this->actingAs($actor)->put(route('human_resources.job_category.update_users', $jobCategory->id), $data);

        // Then job category users relation should have length equal to 5
        $jobCategory->refresh();
        $this->assertCount(5, $jobCategory->users);
        $this->assertEquals($usersCollection->merge($additionalUsers)->pluck('id')->toArray(), $jobCategory->users->pluck('id')->toArray());
        // And detached users should loose their job category id
        $this->assertEquals(null, $detachUsers[0]->refresh()->job_category_id);
        $this->assertEquals(null, $detachUsers[1]->refresh()->job_category_id);
        // And detached users should have logs
        $this->assertCount(2, $detachUsers[0]->refresh()->logs);
    }
}

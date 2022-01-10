<?php

namespace Tests\Feature\HumanResources;

use App\Models\HumanResources\Brigade;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BrigadeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function brigade_must_have_author()
    {
        // Given brigade without foreman
        $brigade = factory(Brigade::class)->create();

        // Then brigade must have author relation
        $this->assertNotNull($brigade->author);
    }

    /** @test */
    public function we_can_create_brigade_without_foreman()
    {
        // Given brigade without foreman
        $brigade = factory(Brigade::class)->create();

        // Then brigade must not have the foreman relation
        $this->assertNull($brigade->foreman);
        $this->assertEquals('Не указан', $brigade->foreman_name);
    }

    /** @test */
    public function we_can_create_brigade_with_foreman()
    {
        // Given foreman
        $foreman = factory(User::class)->create();
        // Given brigade with foreman
        $brigade = factory(Brigade::class)->create(['foreman_id' => $foreman->id]);

        // Then brigade must have the foreman relation
        $this->assertInstanceOf(User::class, $brigade->foreman);
        $this->assertEquals($foreman->id, $brigade->foreman->id);
        $this->assertEquals($foreman->full_name, $brigade->foreman_name);
    }

    /** @test */
    public function brigade_can_have_no_users()
    {
        // Given brigade without users
        $brigade = factory(Brigade::class)->create();

        // Then brigade must have the foreman relation
        $this->assertInstanceOf(Collection::class, $brigade->users);
        $this->assertEmpty($brigade->users);
    }

    /** @test */
    public function brigade_can_have_users()
    {
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given users from brigade
        $users = factory(User::class, 2)->create(['brigade_id' => $brigade->id]);

        // Then brigade must have the users relation
        $this->assertNotEmpty($brigade->users);
        $this->assertEquals($users->pluck('id'), $brigade->users->pluck('id'));
    }

    /** @test */
    public function user_without_permission_cannot_reach_brigade_routes()
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 25, 'is_su' => 0]);

        // When user make get request to /human_resources/brigade
        $response = $this->actingAs($user)->get(route('human_resources.brigade.index'));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_without_permission_cannot_create_brigade()
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 25, 'is_su' => 0]);

        // When user make post request with any data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_create_brigade_without_number_and_direction()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When user make post request without data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then session should have errors
        $response->assertSessionHasErrors(['number', 'direction']);
    }

    /** @test */
    public function user_with_permission_cannot_create_brigade_with_direction_that_does_not_exist()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When user make post request without data
        $data = [
            'number' => 1,
            'direction' => 777
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then session should have errors
        $response->assertSessionHasErrors('direction');
    }

    /** @test */
    public function user_with_permission_can_create_brigade_without_foreman()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When user make post request with data
        $data = [
            'number' => 1,
            'direction' => 1
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = Brigade::get()->last();
        $this->assertEquals(['number' => $data['number'], 'user_id' => $user->id, 'direction' => $data['direction']], [
            'number' => $createdRow->number,
            'user_id' => $createdRow->user_id,
            'direction' => $createdRow->direction
        ]);
        // And model should have one log
        $this->assertCount(1, $createdRow->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $createdRow->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->pluck('id'), $notifications->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Была добавлена новая бригада {$createdRow->number}, бригадир: {$createdRow->foreman_name}");
        $this->assertEquals($notifications->first()->type, 96);
        // Brigade should have empty foreman relation
        $this->assertNull($createdRow->foreman);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.brigade.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_can_create_brigade_with_foreman()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When user make post request with data
        $data = [
            'number' => 1,
            'direction' => 1,
            'foreman_id' => $user->id
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = Brigade::get()->last();
        $this->assertEquals(['number' => $data['number'], 'user_id' => $user->id, 'direction' => $data['direction']], [
            'number' => $createdRow->number,
            'user_id' => $createdRow->user_id,
            'direction' => $createdRow->direction
        ]);
        // And model should have one log
        $this->assertCount(1, $createdRow->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $createdRow->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->pluck('id'), $notifications->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Была добавлена новая бригада {$createdRow->number}, бригадир: {$createdRow->foreman_name}");
        $this->assertEquals($notifications->first()->type, 96);
        // Brigade should have foreman relation
        $this->assertEquals($user->id, $createdRow->foreman->id);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.brigade.show', $createdRow->id));
    }

    /** @test */
    public function user_with_permission_cannot_create_brigade_with_foreman_if_foreman_was_in_some_brigade_as_worker()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given user from brigade
        $foreman = factory(User::class)->create(['brigade_id' => $brigade->id]);

        // When user make post request with data
        $data = [
            'number' => 1,
            'direction' => 1,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then ...
        // Session should have error
        $response->assertSessionHasErrors('user_in_other_brigade');
        $this->assertEquals(session()->get('errors')->default->get('user_in_other_brigade')[0], $brigade->number);
    }

    /** @test */
    public function user_with_permission_can_create_brigade_with_foreman_if_foreman_was_in_some_brigade_as_worker_after_additional_agreement()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given user from brigade
        $foreman = factory(User::class)->create(['brigade_id' => $brigade->id]);

        // When user make post request with data
        $data = [
            'number' => 1,
            'direction' => 1,
            'foreman_id' => $foreman->id,
            'skip_other_brigade_check' => 1
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = Brigade::get()->last();
        $this->assertEquals(['number' => $data['number'], 'user_id' => $user->id, 'direction' => $data['direction'], 'foreman_id' => $data['foreman_id']], [
            'number' => $createdRow->number,
            'user_id' => $createdRow->user_id,
            'direction' => $createdRow->direction,
            'foreman_id' => $createdRow->foreman_id,
        ]);
        // And foreman should loose brigade relation
        $this->assertEquals(null, $foreman->refresh()->brigade_id);
    }

    /** @test */
    public function user_with_permission_cannot_create_brigade_with_foreman_if_foreman_was_in_some_brigade_as_foreman()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given user
        $foreman = factory(User::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create(['foreman_id' => $foreman->id]);

        // When user make post request with data
        $data = [
            'number' => 1,
            'direction' => 1,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then ...
        // Session should have error
        $response->assertSessionHasErrors('foreman_in_other_brigade');
        $this->assertEquals(session()->get('errors')->default->get('foreman_in_other_brigade')[0], $brigade->number);
    }

    /** @test */
    public function user_with_permission_can_create_brigade_with_foreman_if_foreman_was_in_some_brigade_as_foreman_after_additional_agreement()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given user
        $foreman = factory(User::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create(['foreman_id' => $foreman->id]);

        // When user make post request with data
        $data = [
            'number' => 1,
            'direction' => 1,
            'foreman_id' => $foreman->id,
            'skip_other_brigade_foreman_check' => 1
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.store'), $data);

        // Then ...
        // We must see new example in database
        $createdRow = Brigade::get()->last();
        $this->assertEquals(['number' => $data['number'], 'user_id' => $user->id, 'direction' => $data['direction'], 'foreman_id' => $data['foreman_id']], [
            'number' => $createdRow->number,
            'user_id' => $createdRow->user_id,
            'direction' => $createdRow->direction,
            'foreman_id' => $createdRow->foreman_id,
        ]);
        // And brigade should loose foreman relation
        $this->assertEquals(null, $brigade->refresh()->foreman_id);
    }

    /** @test */
    public function user_without_permission_cannot_destroy_brigade()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When user make delete request
        $response = $this->actingAs($user)->delete(route('human_resources.brigade.destroy', $brigade->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_destroy_brigade()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade with foreman
        $brigade = factory(Brigade::class)->create(['foreman_id' => $user->id]);
        // Given users related to brigade
        $user1 = factory(User::class)->create(['brigade_id' => $brigade->id]);
        $user2 = factory(User::class)->create(['brigade_id' => $brigade->id]);
        $user3 = factory(User::class)->create(['brigade_id' => $brigade->id]);

        // When user make delete request
        $response = $this->actingAs($user)->delete(route('human_resources.brigade.destroy', $brigade->id));

        // Then ...
        // Brigade should be deleted
        $brigade->refresh();
        $this->assertSoftDeleted($brigade);
        // And brigade should have two logs
        $this->assertCount(2, $brigade->logs);

        // And brigade should loose foreman
        $this->assertNull($brigade->foreman);
        // And users should loose their brigade
        $this->assertEquals([null, null, null], [$user1->refresh()->brigade_id, $user2->refresh()->brigade_id, $user3->refresh()->brigade_id]);
        // And users should have log about it
        $this->assertCount(1, $user1->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications->where('type', 97);
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->get()->push($user)->unique('id')->sortBy('id')->pluck('id'), $notifications->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Бригада номер {$brigade->number} была удалена сотрудником {$user->full_name}");
        $this->assertEquals($notifications->first()->type, 97);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.brigade.index'));
    }

    /** @test */
    public function user_with_permission_can_destroy_brigade_without_foreman()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade without foreman
        $brigade = factory(Brigade::class)->create(['foreman_id' => null]);
        // Given users related to brigade
        $user1 = factory(User::class)->create(['brigade_id' => $brigade->id]);
        $user2 = factory(User::class)->create(['brigade_id' => $brigade->id]);
        $user3 = factory(User::class)->create(['brigade_id' => $brigade->id]);

        // When user make delete request
        $response = $this->actingAs($user)->delete(route('human_resources.brigade.destroy', $brigade->id));

        // Then ...
        // Brigade should be deleted
        $brigade->refresh();
        $this->assertSoftDeleted($brigade);
        // And brigade should have two logs
        $this->assertCount(2, $brigade->logs);
        // And brigade should loose foreman
        $this->assertNull($brigade->foreman);
        // And users should loose their brigade
        $this->assertEquals([null, null, null], [$user1->refresh()->brigade_id, $user2->refresh()->brigade_id, $user3->refresh()->brigade_id]);
        // And users should have log about it
        $this->assertCount(1, $user1->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications->where('type', 97);
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->get()->unique('id')->sortBy('id')->pluck('id'), $notifications->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->first()->name, "Бригада номер {$brigade->number} была удалена сотрудником {$user->full_name}");
        $this->assertEquals($notifications->first()->type, 97);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.brigade.index'));
    }

    /** @test */
    public function brigade_filter_work_without_any_filters_and_brigades()
    {
        // Given no brigades
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.brigade.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains nothing
        $this->assertCount(0, $response['data']['brigades']);
    }

    /** @test */
    public function brigade_filter_work_without_any_filters()
    {
        // Given three brigades
        $brigades = factory(Brigade::class, 3)->create();
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.brigade.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains three brigades
        $this->assertCount(3, $response['data']['brigades']);
        $this->assertEquals($brigades->pluck('id'), collect($response['data']['brigades'])->pluck('id'));
        // And brigades should have users count
        $this->assertArrayHasKey('users_count', $response['data']['brigades'][0]);
    }

    /** @test */
    public function brigade_filter_by_number()
    {
        // Given three brigades
        factory(Brigade::class, 2)->create();
        $brigade = factory(Brigade::class)->create();
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.brigade.paginated', ['url' => route('human_resources.brigade.index') . "?number={$brigade->number}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one brigade
        $this->assertCount(1, $response['data']['brigades']);
        // Especial one
        $this->assertEquals($response['data']['brigades'][0]['id'], $brigade->id);
        // And brigades should have users count
        $this->assertArrayHasKey('users_count', $response['data']['brigades'][0]);
    }

    /** @test */
    public function brigade_filter_by_numbers()
    {
        // Given three brigades
        factory(Brigade::class, 2)->create();
        $brigade1 = factory(Brigade::class)->create();
        $brigade2 = factory(Brigade::class)->create();
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.brigade.paginated', ['url' => route('human_resources.brigade.index') . "?number%5B0%5D={$brigade1->number}&number%5B1%5D={$brigade2->number}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains two brigades
        $this->assertCount(2, $response['data']['brigades']);
        // Especial ones
        $this->assertEquals($response['data']['brigades'][0]['id'], $brigade1->id);
        $this->assertEquals($response['data']['brigades'][1]['id'], $brigade2->id);
        // And brigades should have users count
        $this->assertArrayHasKey('users_count', $response['data']['brigades'][0]);
    }

    /** @test */
    public function brigade_filter_by_direction()
    {
        // Given three brigades
        factory(Brigade::class, 2)->create(['direction' => 1]);
        $brigade = factory(Brigade::class)->create(['direction' => 2]);
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.brigade.paginated', ['url' => route('human_resources.brigade.index') . "?direction={$brigade->direction}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one brigade
        $this->assertCount(1, $response['data']['brigades']);
        // Especial one
        $this->assertEquals($response['data']['brigades'][0]['id'], $brigade->id);
        // And brigade should have users count
        $this->assertArrayHasKey('users_count', $response['data']['brigades'][0]);
    }

    /** @test */
    public function brigade_filter_by_directions()
    {
        // Given three brigades
        factory(Brigade::class)->create(['direction' => 3]);
        $brigade1 = factory(Brigade::class)->create(['direction' => 1]);
        $brigade2 = factory(Brigade::class)->create(['direction' => 2]);
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(
            route('human_resources.brigade.paginated', ['url' => route('human_resources.brigade.index') . "?direction%5B0%5D={$brigade1->direction}&direction%5B1%5D={$brigade2->direction}"])
        )->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains two brigades
        $this->assertCount(2, $response['data']['brigades']);
        // Brigade 1 and 2
        $this->assertEquals([$response['data']['brigades'][0]['id'], $response['data']['brigades'][1]['id']], [$brigade1->id, $brigade2->id]);
        // And brigades should have users count
        $this->assertArrayHasKey('users_count', $response['data']['brigades'][0]);
    }

    /** @test */
    public function brigade_filter_by_foreman()
    {
        // Given three brigades
        factory(Brigade::class, 2)->create(['foreman_id' => null]);
        $brigade = factory(Brigade::class)->create(['foreman_id' => 2]);
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(route('human_resources.brigade.paginated', ['url' => route('human_resources.brigade.index') . "?foreman_id={$brigade->foreman_id}"]))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains one brigade
        $this->assertCount(1, $response['data']['brigades']);
        // Especial one
        $this->assertEquals($response['data']['brigades'][0]['id'], $brigade->id);
        // And brigade should have users count
        $this->assertArrayHasKey('users_count', $response['data']['brigades'][0]);
    }

    /** @test */
    public function brigade_filter_by_foremans()
    {
        // Given three brigades
        factory(Brigade::class)->create(['foreman_id' => null]);
        $brigade1 = factory(Brigade::class)->create(['foreman_id' => 1]);
        $brigade2 = factory(Brigade::class)->create(['foreman_id' => 2]);
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();

        // When we make post request
        $response = $this->actingAs($user)->post(
            route('human_resources.brigade.paginated', ['url' => route('human_resources.brigade.index') . "?foreman_id%5B0%5D={$brigade1->foreman_id}&foreman_id%5B1%5D={$brigade2->foreman_id}"])
        )->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains two brigades
        $this->assertCount(2, $response['data']['brigades']);
        // Brigade 1 and 2
        $this->assertEquals([$response['data']['brigades'][0]['id'], $response['data']['brigades'][1]['id']], [$brigade1->id, $brigade2->id]);
        // And brigades should have users count
        $this->assertArrayHasKey('users_count', $response['data']['brigades'][0]);
    }

    /** @test */
    public function brigades_foreman_getter_can_return_foremans_without_any_params()
    {
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foremans
        $foremans = factory(User::class, 3)->create();
        // Given three brigades
        $brigade1 = factory(Brigade::class)->create(['foreman_id' => $foremans[0]->id]);
        $brigade2 = factory(Brigade::class)->create(['foreman_id' => $foremans[1]->id]);
        $brigade3 = factory(Brigade::class)->create(['foreman_id' => $foremans[2]->id]);

        // When we make get request
        $response = $this->actingAs($user)->get(route('users::get_foreman_for_brigades'))->json();

        // Then ...
        // We must have three users in response
        $this->assertCount(3, $response);
        // And this users must be user from foremans variable
        $this->assertEquals($foremans->pluck('id'), collect($response)->pluck('code'));
    }

    /** @test */
    public function brigades_foreman_getter_can_return_no_one()
    {
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given three brigades
        $brigade1 = factory(Brigade::class)->create(['foreman_id' => null]);
        $brigade2 = factory(Brigade::class)->create(['foreman_id' => null]);
        $brigade3 = factory(Brigade::class)->create(['foreman_id' => null]);

        // When we make get request
        $response = $this->actingAs($user)->get(route('users::get_foreman_for_brigades'))->json();

        // Then ...
        // We must have no users in response
        $this->assertCount(0, $response);
    }

    /** @test */
    public function brigades_foreman_getter_return_foremans_by_name()
    {
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foremans
        $foremans = factory(User::class, 3)->create();
        // Given three brigades
        $brigade1 = factory(Brigade::class)->create(['foreman_id' => $foremans[0]->id]);
        $brigade2 = factory(Brigade::class)->create(['foreman_id' => $foremans[1]->id]);
        $brigade3 = factory(Brigade::class)->create(['foreman_id' => $foremans[2]->id]);

        // When we make get request
        $response = $this->actingAs($user)->get(route('users::get_foreman_for_brigades', ['q' => $foremans[0]->first_name]))->json();

        // Then ...
        // We must have one user in response
        $this->assertCount(1, $response);
        // And this user must be first foreman
        $this->assertEquals($foremans[0]->id, $response[0]['code']);
    }

    /** @test */
    public function user_without_permission_cannot_update_brigade()
    {
        // Given user without permission
        $user = User::whereNotIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When user make update request
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id));

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_without_number_and_direction()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When user make update request without data
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id));

        // Then user should have errors
        $response->assertSessionHasErrors(['number', 'direction']);
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_with_number_that_already_exists()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigades
        $brigade = factory(Brigade::class)->create();
        $brigade1 = factory(Brigade::class)->create();

        // When user make update request with data
        $data = [
            'number' => $brigade1->number,
            'direction' => $brigade->direction
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors(['number']);
    }

    /** @test */
    public function user_with_permission_can_update_brigade_number_to_same_number()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigades
        $brigade = factory(Brigade::class)->create();

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should not have errors
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_foreman_to_person_that_already_in_some_brigade()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigades
        $brigade = factory(Brigade::class)->create();
        $brigade2 = factory(Brigade::class)->create();
        // Given foreman as person from second brigade
        $foreman = factory(User::class)->create(['brigade_id' => $brigade2->id]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors(['user_in_other_brigade']);
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_foreman_to_person_that_already_in_some_brigade_but_can_after_accept()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigades
        $brigade = factory(Brigade::class)->create();
        $brigade2 = factory(Brigade::class)->create();
        // Given foreman as person from second brigade
        $foreman = factory(User::class)->create(['brigade_id' => $brigade2->id]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors(['user_in_other_brigade']);

        // Then user will send one more request (if accept)
        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id,
            'skip_other_brigade_check' => 1
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);
        // Then user should not have errors
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_foreman_to_person_that_already_is_foreman_in_some_brigade()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foreman
        $foreman = factory(User::class)->create();
        // Given brigades
        $brigade = factory(Brigade::class)->create();
        $brigade2 = factory(Brigade::class)->create(['foreman_id' => $foreman->id]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors(['foreman_in_other_brigade']);
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_foreman_to_person_that_already_is_foreman_in_some_brigade_but_can_after_accept()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foreman
        $foreman = factory(User::class)->create();
        // Given brigades
        $brigade = factory(Brigade::class)->create();
        $brigade2 = factory(Brigade::class)->create(['foreman_id' => $foreman->id]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors(['foreman_in_other_brigade']);

        // Then user will send one more request (if accept)
        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id,
            'skip_other_brigade_foreman_check' => 1
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);
        // Then user should not have errors
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function user_with_permission_can_update_brigade_foreman_to_same_person()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foreman
        $foreman = factory(User::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create(['foreman_id' => $foreman->id]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should not have errors
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function user_with_permission_can_update_brigade_by_removing_foreman()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foreman
        $foreman = factory(User::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create(['foreman_id' => $foreman->id,]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => null
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should not have errors
        $response->assertSessionHasNoErrors();
        // And brigade should be updated and lost foreman
        $brigade->refresh();
        $this->assertEquals(['number' => $data['number'], 'user_id' => $user->id, 'direction' => $data['direction'], 'foreman_id' => $data['foreman_id']], [
            'number' => $brigade->number,
            'user_id' => $brigade->user_id,
            'direction' => $brigade->direction,
            'foreman_id' => $brigade->foreman_id]);
    }

    /** @test */
    public function user_with_permission_can_update_brigade_by_removing_foreman_and_some_things_should_happen()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foreman
        $foreman = factory(User::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create(['foreman_id' => $foreman->id,]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => null
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should not have errors
        $response->assertSessionHasNoErrors();
        // And brigade should be updated and lost foreman
        $brigade->refresh();
        $this->assertEquals(['number' => $data['number'], 'user_id' => $user->id, 'direction' => $data['direction'], 'foreman_id' => $data['foreman_id']], [
            'number' => $brigade->number,
            'user_id' => $brigade->user_id,
            'direction' => $brigade->direction,
            'foreman_id' => $brigade->foreman_id]);
        // And model should have two logs
        $this->assertCount(2, $brigade->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->pluck('id'), $notifications->where('type', 98)->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->sortByDesc('id')->first()->name, "Бригада номер {$brigade->number} была изменена сотрудником {$user->full_name}");
        $this->assertEquals($notifications->sortByDesc('id')->first()->type, 98);
        // Brigade should have empty foreman relation
        $this->assertNull($brigade->foreman);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.brigade.show', $brigade->id));
    }

    /** @test */
    public function user_with_permission_can_update_brigade_by_updating_foreman_and_some_things_should_happen()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given foreman
        $foreman = factory(User::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create(['foreman_id' => null]);

        // When user make update request with data
        $data = [
            'number' => $brigade->number,
            'direction' => $brigade->direction,
            'brigade_id' => $brigade->id,
            'foreman_id' => $foreman->id
        ];
        $response = $this->actingAs($user)->put(route('human_resources.brigade.update', $brigade->id), $data);

        // Then user should not have errors
        $response->assertSessionHasNoErrors();
        // And brigade should be updated and lost foreman
        $brigade->refresh();
        $this->assertEquals(['number' => $data['number'], 'user_id' => $user->id, 'direction' => $data['direction'], 'foreman_id' => $data['foreman_id']], [
            'number' => $brigade->number,
            'user_id' => $brigade->user_id,
            'direction' => $brigade->direction,
            'foreman_id' => $brigade->foreman_id]);
        // And model should have two logs
        $this->assertCount(2, $brigade->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->get()->push($foreman)->unique('id')->pluck('id'), $notifications->where('type', 98)->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->sortByDesc('id')->first()->name, "Бригада номер {$brigade->number} была изменена сотрудником {$user->full_name}");
        $this->assertEquals($notifications->sortByDesc('id')->first()->type, 98);
        // Brigade should have foreman relation
        $this->assertEquals($foreman->id, $brigade->foreman->id);
        // User should be redirected to show
        $response = $response->json();
        $this->assertEquals($response['redirect'], route('human_resources.brigade.show', $brigade->id));
    }

    /** @test */
    public function user_without_permission_cannot_update_brigade_users()
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 25, 'is_su' => 0]);
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When user make post request with any data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_update_brigade_users_by_adding_new_users()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given users
        $users = factory(User::class, 3)->create();

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => $users->pluck('id')->toArray()
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Brigade users relation should have count 3
        $this->assertCount(3, $brigade->refresh()->users);
        $this->assertEquals($users->pluck('id'), $brigade->users->pluck('id'));
        // Brigade should have 2 logs
        $this->assertCount(2, $brigade->logs);
        // Users should have one log
        $this->assertCount(1, $brigade->users[0]->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->pluck('id'), $notifications->where('type', 99)->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->sortByDesc('id')->first()->name, "Личный состав бригады номер {$brigade->number} была изменен сотрудником {$user->full_name}");
        $this->assertEquals($notifications->sortByDesc('id')->first()->type, 99);
        // Users should be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $users->pluck('id'));
    }

    /** @test */
    public function user_with_permission_can_update_brigade_users_by_adding_new_users_into_brigade_with_users()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given users from brigade
        $oldUsers = factory(User::class, 3)->create(['brigade_id' => $brigade->id]);
        // Given new users
        $users = factory(User::class, 3)->create();

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => $users->pluck('id')->toArray()
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Brigade users relation should have count 6
        $this->assertCount(6, $brigade->refresh()->users);
        $this->assertEquals($oldUsers->merge($users)->pluck('id'), $brigade->users->pluck('id'));
        // Brigade should have 2 logs
        $this->assertCount(2, $brigade->logs);
        // Users should have one log
        $this->assertCount(1, $brigade->users[5]->logs);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->pluck('id'), $notifications->where('type', 99)->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->sortByDesc('id')->first()->name, "Личный состав бригады номер {$brigade->number} была изменен сотрудником {$user->full_name}");
        $this->assertEquals($notifications->sortByDesc('id')->first()->type, 99);
        // Users should be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $oldUsers->merge($users)->pluck('id'));
    }

    /** @test */
    public function user_with_permission_can_update_brigade_users_by_removing_users_from_brigade()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given users from brigade
        $oldUsers = factory(User::class, 2)->create(['brigade_id' => $brigade->id]);
        $oldUser = factory(User::class)->create(['brigade_id' => $brigade->id]);

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => $oldUsers->pluck('id')->toArray(),
            'deleted_user_ids' => [$oldUser->id]
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Brigade users relation should have count 2
        $this->assertCount(2, $brigade->refresh()->users);
        $this->assertEquals($oldUsers->pluck('id'), $brigade->users->pluck('id'));
        // Brigade should have 2 logs
        $this->assertCount(2, $brigade->logs);
        // User should have one log
        $this->assertCount(1, $oldUser->logs);
        // And lose brigade id
        $this->assertNull($user->brigade_id);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->pluck('id'), $notifications->where('type', 99)->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->sortByDesc('id')->first()->name, "Личный состав бригады номер {$brigade->number} была изменен сотрудником {$user->full_name}");
        $this->assertEquals($notifications->sortByDesc('id')->first()->type, 99);
        // Users should be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $oldUsers->pluck('id'));
    }

    /** @test */
    public function user_with_permission_can_update_brigade_users_by_adding_and_removing_users()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade
        $brigade = factory(Brigade::class)->create();
        // Given users from brigade
        $oldUsers = factory(User::class, 2)->create(['brigade_id' => $brigade->id]);
        $oldUser = factory(User::class)->create(['brigade_id' => $brigade->id]);
        // Given new users
        $users = factory(User::class, 2)->create();

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => $oldUsers->merge($users)->pluck('id')->toArray(),
            'deleted_user_ids' => [$oldUser->id]
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Brigade users relation should have count 4
        $this->assertCount(4, $brigade->refresh()->users);
        $this->assertEquals($oldUsers->merge($users)->pluck('id'), $brigade->users->pluck('id'));
        // Brigade should have 2 logs
        $this->assertCount(2, $brigade->logs);
        // Users should have one log
        $this->assertCount(1, $users[0]->logs);
        // User should have one log
        $this->assertCount(1, $oldUser->logs);
        // And lose brigade id
        $this->assertNull($user->brigade_id);
        // Some notifications should be generated for RPs and Main Engineer
        $notifications = $brigade->notifications;
        $this->assertEquals(User::whereIn('group_id', [8, 13, 19, 27])->where('status', 1)->pluck('id'), $notifications->where('type', 99)->sortBy('user_id')->pluck('user_id'));
        // With some text and type
        $this->assertEquals($notifications->sortByDesc('id')->first()->name, "Личный состав бригады номер {$brigade->number} была изменен сотрудником {$user->full_name}");
        $this->assertEquals($notifications->sortByDesc('id')->first()->type, 99);
        // Users should be returned from response
        $response = $response->json();
        $this->assertEquals(collect($response['users'])->pluck('id'), $oldUsers->merge($users)->pluck('id'));
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_users_by_adding_users_that_was_in_other_brigade()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigades
        $oldBrigade = factory(Brigade::class)->create();
        $brigade = factory(Brigade::class)->create();
        // Given users from other brigade
        $users = factory(User::class, 3)->create(['brigade_id' => $oldBrigade->id]);

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => $users->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('override');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('override')[0], json_encode($users->pluck('id')->toArray()));
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_users_by_adding_brigade_foreman_as_user()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade with foreman
        $brigade = factory(Brigade::class)->create(['foreman_id' => $user->id]);

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => [$user->id],
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('add_foreman_as_brigade_user');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('add_foreman_as_brigade_user')[0], json_encode([$user->id]));
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_users_by_adding_any_brigade_foreman_as_user()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigade with foreman
        $brigade = factory(Brigade::class)->create();
        $brigade2 = factory(Brigade::class)->create(['foreman_id' => $user->id]);

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => [$user->id],
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('add_foreman_as_brigade_user');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('add_foreman_as_brigade_user')[0], json_encode([$user->id]));
    }

    /** @test */
    public function user_with_permission_cannot_update_brigade_users_by_adding_users_that_was_in_other_brigade_but_can_after_accept()
    {
        // Given user with permission
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigades
        $oldBrigade = factory(Brigade::class)->create();
        $brigade = factory(Brigade::class)->create();
        // Given users from other brigade
        $users = factory(User::class, 3)->create(['brigade_id' => $oldBrigade->id]);

        // When user make post request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => $users->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);

        // Then ...
        // Session should have errors
        $response->assertSessionHasErrors('override');
        // With IDs of job categories
        $this->assertEquals(session()->get('errors')->default->get('override')[0], json_encode($users->pluck('id')->toArray()));

        // Then user will send one more request (if accept)
        // When user make update request with data
        $data = [
            'brigade_id' => $brigade->id,
            'user_ids' => $users->pluck('id')->toArray(),
            'skip_users_check' => 1
        ];
        $response = $this->actingAs($user)->post(route('human_resources.brigade.update_users', $brigade->id), $data);
        // Then user should not have errors
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function brigade_can_be_appointed_to_project()
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given brigade
        $brigade = factory(Brigade::class)->create();

        // When we add brigade on project
        $project->brigades()->save($brigade);

        // Then brigade should have appointments() relation with count 1
        $this->assertCount(1, $brigade->refresh()->appointments);
        $this->assertEquals($project->id, $brigade->appointments[0]->id);
    }

    /** @test */
    public function brigades_getter_can_return_brigades_without_any_params()
    {
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given brigades
        $brigades = factory(Brigade::class, 3)->create();

        // When we make get request
        $response = $this->actingAs($user)->get(route('human_resources.brigade.get_brigades'))->json();

        // Then ...
        // We must have three brigades in response
        $this->assertCount(3, $response);
        // And this brigades must be brigades from brigades variable
        $this->assertEquals($brigades->pluck('id'), collect($response)->pluck('code'));
    }

    /** @test */
    public function brigades_getter_will_return_only_six_brigades_per_time()
    {
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given 10 brigades
        $brigades = factory(Brigade::class, 10)->create();

        // When we make get request
        $response = $this->actingAs($user)->get(route('human_resources.brigade.get_brigades'))->json();

        // Then ...
        // We must have six brigades in response
        $this->assertCount(6, $response);
        // And this brigades must be brigades from brigades variable
        $this->assertEquals($brigades->take(6)->pluck('id'), collect($response)->pluck('code'));
    }

    /** @test */
    public function brigades_getter_can_return_nothing()
    {
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given no brigades
        Brigade::query()->delete();

        // When we make get request
        $response = $this->actingAs($user)->get(route('human_resources.brigade.get_brigades'))->json();

        // Then ...
        // We must have no brigades in response
        $this->assertCount(0, $response);
    }

    /** @test */
    public function brigades_getter_return_brigades_by_number()
    {
        // Given user
        $user = User::whereIn('group_id', [5, 6, 8])->inRandomOrder()->first();
        // Given three brigades
        $brigades = factory(User::class, 2)->create();
        $brigade = factory(Brigade::class)->create(['number' => '777']);

        // When we make get request
        $response = $this->actingAs($user)->get(route('human_resources.brigade.get_brigades', ['q' => $brigade->number]))->json();

        // Then ...
        // We must have one brigade in response
        $this->assertCount(1, $response);
        // And this brigade must be first $brigade
        $this->assertEquals($brigade->id, $response[0]['code']);
    }
}

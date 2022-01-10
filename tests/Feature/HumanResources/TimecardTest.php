<?php

namespace Tests\Feature\HumanResources;

use App\Models\HumanResources\{Brigade, TariffRates, Timecard, TimecardAddition, TimecardDay, TimecardRecord};
use App\Models\{Notification, Project, ProjectResponsibleUser, User};
use App\Models\Contract\Contract;
use Carbon\Carbon;
use Tests\TestCase;

class TimecardTest extends TestCase
{
    public const ABLE_TO_MANAGE_TIMECARD = [5, 6, 8, 13, 19, 27];
    public const ABLE_TO_FILL_TIMECARD = [5, 6, 8, 13, 14, 19, 23, 27, 31];
    public const ABLE_TO_GENERATE_REPORT = [5, 6];

    /** @test */
    public function we_can_create_timecard(): void
    {
        // When we use factory
        $timecard = factory(Timecard::class)->create();

        // Then we must have Timecard class instance
        $this->assertInstanceOf(Timecard::class, $timecard);
    }

    /** @test */
    public function base_timecard_description(): void
    {
        // When we use factory
        $timecard = factory(Timecard::class)->create();

        // Then Timecard instance should have ...
        // User relation
        $this->assertInstanceOf(User::class, $timecard->user);
        // Author relation
        $this->assertInstanceOf(User::class, $timecard->author);
        // Month property
        $this->assertContains($timecard->month, range(1, Carbon::MONTHS_PER_YEAR));
        // KTU property equal to zero
        $this->assertEquals(0, $timecard->ktu);
        // And timecard should be opened
        $this->assertEquals(1, $timecard->is_opened);
    }

    /** @test */
    public function timecard_have_user_and_author_relation(): void
    {
        // Given user
        $user = factory(User::class)->create();
        // Given author
        $author = factory(User::class)->create();

        // When we use factory
        $timecard = factory(Timecard::class)->create(['user_id' => $user->id, 'author_id' => $author->id]);

        // Then Timecard instance should have user and author relation to same users as we provided
        $this->assertEquals($user->id, $timecard->user->id);
        $this->assertEquals($author->id, $timecard->author->id);
    }

    /** @test */
    public function user_without_permission_cannot_open_timecard(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);

        // When user make put request with any data
        $data = [];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_open_opened_timecard(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = ['is_opened' => 1, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_opened');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_opened')[0], 'Данный табель уже открыт, его нельзя открыть ещё раз');
    }

    /** @test */
    public function user_with_permission_can_open_closed_timecard(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);

        // When user make put request with data
        $data = ['is_opened' => 1, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then everything should be ok
        $response->assertSessionHasNoErrors();
        // And timecard should be opened
        $this->assertEquals($data['is_opened'], $timecard->refresh()->is_opened);
        // And timecard should have logs
        $this->assertCount(1, $timecard->logs);
    }

    /** @test */
    public function user_without_permission_cannot_close_timecard(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with any data
        $data = [];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_close_closed_timecard(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);

        // When user make put request with data
        $data = ['is_opened' => 0, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель уже закрыт, его нельзя закрыть ещё раз');
    }

    /** @test */
    public function user_with_permission_can_close_opened_timecard(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = ['is_opened' => 0, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then everything should be ok
        $response->assertSessionHasNoErrors();
        // And timecard should be closed
        $this->assertEquals($data['is_opened'], $timecard->refresh()->is_opened);
        // And timecard should have logs
        $this->assertCount(1, $timecard->logs);
    }

    /** @test */
    public function user_with_permission_can_close_opened_timecard_and_then_open_it_again(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = ['is_opened' => 0, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then everything should be ok
        $response->assertSessionHasNoErrors();
        // And timecard should be closed
        $this->assertEquals($data['is_opened'], $timecard->refresh()->is_opened);
        // And timecard should have logs
        $this->assertCount(1, $timecard->logs);

        // When user make put request with data
        $data = ['is_opened' => 1, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_openness', $timecard->id), $data);

        // Then everything should be ok
        $response->assertSessionHasNoErrors();
        // And timecard should be opened
        $this->assertEquals($data['is_opened'], $timecard->refresh()->is_opened);
        // And timecard should have logs
        $this->assertCount(2, $timecard->logs);
    }

    /** @test */
    public function user_without_permission_cannot_update_timecard_KTU(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with any data
        $data = [];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_ktu', $timecard->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_cannot_update_KTU_on_closed_timecard(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);

        // When user make put request with data
        $data = ['ktu' => 69, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_ktu', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель закрыт, ему нельзя менять КТУ');
    }

    /** @test */
    public function user_with_permission_cannot_set_KTU_more_than_one_hundred(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = ['ktu' => 148, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_ktu', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('ktu');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('ktu')[0], 'КТУ изменяется в пределах от 0 до 100');
    }

    /** @test */
    public function user_with_permission_cannot_set_KTU_less_than_zero(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = ['ktu' => -69, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_ktu', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('ktu');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('ktu')[0], 'КТУ изменяется в пределах от 0 до 100');
    }

    /** @test */
    public function user_with_permission_can_update_KTU_on_opened_timecard(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = ['ktu' => 69, 'timecard_id' => $timecard->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_ktu', $timecard->id), $data);

        // Then everything should be ok
        $response->assertSessionHasNoErrors();
        // And timecard should have provided ktu
        $this->assertEquals($data['ktu'], $timecard->refresh()->ktu);
        // And timecard should have logs
        $this->assertCount(1, $timecard->logs);
    }

    /** @test */
    public function timecard_can_have_additions(): void
    {
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Given timecard addition
        $addition = factory(TimecardAddition::class)->create();

        // When we save addition in timecard
        $timecard->additions()->save($addition);

        // Then timecard should have additions() relation with count 1
        $this->assertCount(1, $timecard->refresh()->additions);
        $this->assertEquals($addition->id, $timecard->additions[0]->id);
    }

    /** @test */
    public function timecard_can_have_additions_reachable_by_type(): void
    {
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Given timecard additions
        $compensation = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['compensation']]);
        $fine = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['fine']]);
        $bonus = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['bonus']]);

        // When we save additions in timecard
        $timecard->additions()->saveMany([$compensation, $fine, $bonus]);

        // Then timecard should have additions() relation with count 3
        $this->assertCount(3, $timecard->refresh()->additions);
        $this->assertEquals($compensation->id, $timecard->compensations[0]->id);
        $this->assertEquals($fine->id, $timecard->fines[0]->id);
        $this->assertEquals($bonus->id, $timecard->bonuses[0]->id);
    }

    /** @test */
    public function timecard_addition_should_have_timecard_relation(): void
    {
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Given timecard addition
        $addition = factory(TimecardAddition::class)->create(['timecard_id' => $timecard->id]);

        // Then timecard addition should have timecard() relation
        $this->assertEquals($timecard->id, $addition->timecard->id);
    }

    /** @test */
    public function timecard_addition_should_have_type_name_property(): void
    {
        // Given timecard additions
        $compensation = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['compensation']]);
        $fine = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['fine']]);
        $bonus = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['bonus']]);

        // Then all additions should have proper type name
        $this->assertEquals('Компенсация', $compensation->type_name);
        $this->assertEquals('Штраф', $fine->type_name);
        $this->assertEquals('Премия', $bonus->type_name);
    }

    /** @test */
    public function user_without_permission_cannot_update_timecard_compensations_list(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with any data
        $data = [];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_compensations', $timecard->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_update_timecard_compensations_list_by_creating_new(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'compensations' => [
                [
                    'name' => 'Test',
                    'amount' => 10.00
                ],
                [
                    'name' => 'Test 2',
                    'amount' => 20.00
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_compensations', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have two additions
        $this->assertCount(2, $timecard->refresh()->additions);
        $this->assertEquals(collect($data['compensations'])->pluck('name'), $timecard->additions->pluck('name'));
        $this->assertEquals(collect($data['compensations'])->pluck('amount'), $timecard->additions->pluck('amount'));
        $this->assertEquals(TimecardAddition::TYPES_ENG['compensation'], $timecard->additions[0]->type);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Compensations should have log too
        $compensation = $timecard->additions->first();
        $this->assertCount(1, $compensation->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_compensations_list_by_deleting_all_compensations(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With compensations
        $compensations = factory(TimecardAddition::class, 2)->create(['type' => TimecardAddition::TYPES_ENG['compensation'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'deleted_addition_ids' => $compensations->pluck('id')->toArray()
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_compensations', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have zero additions
        $this->assertCount(0, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Additions should have two logs (one about creation and one about deletion)
        $addition = $compensations->first()->refresh();
        $this->assertCount(2, $addition->logs);
        // Compensations should be trashed
        $this->assertSoftDeleted($compensations[0]);
        $this->assertSoftDeleted($compensations[1]);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_compensations_list_by_deleting_one_compensation(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With compensations
        $compensations = factory(TimecardAddition::class, 2)->create(['type' => TimecardAddition::TYPES_ENG['compensation'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'deleted_addition_ids' => [$compensations[0]->id]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_compensations', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one addition
        $this->assertCount(1, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Deleted addition should have two logs (one about creation and one about deletion)
        $addition = $compensations->first()->refresh();
        $this->assertCount(2, $addition->logs);
        // Compensation should be trashed
        $this->assertSoftDeleted($compensations[0]);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_compensations_list_by_updating_them(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With compensations
        $compensation = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['compensation'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'compensations' => [
                [
                    'id' => $compensation->id,
                    'name' => 'TAKE',
                    'amount' => 69.00
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_compensations', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one addition
        $this->assertCount(1, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Updated addition should have two logs (one about creation and one about update)
        $this->assertCount(2, $compensation->refresh()->logs);
        // Compensation should be updated
        $this->assertEquals([$data['compensations'][0]['name'], $data['compensations'][0]['amount']], [$compensation->name, $compensation->amount]);
    }

    /** @test */
    public function user_with_permission_cannot_update_timecard_compensations_if_timelist_is_closed(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);
        // With compensations
        $compensation = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['compensation'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'compensations' => [
                [
                    'id' => $compensation->id,
                    'name' => 'TAKE',
                    'amount' => 69.00
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_compensations', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель закрыт, ему нельзя менять список компенсаций');
    }

    /** @test */
    public function prolonged_compensations_influence(): void
    {
        /**
         * If timecard have compensation with prolonged property, then
         * next month timecard should inherit this prolonged compensations
         */
        // Given user
        $user = factory(User::class)->create();
        // Given timecard for march
        $timecard = factory(Timecard::class)->create(['month' => 3, 'user_id' => $user->id]);
        // With prolonged compensation
        $compensation = factory(TimecardAddition::class)->create([
            'type' => TimecardAddition::TYPES_ENG['compensation'],
            'timecard_id' => $timecard->id,
            'prolonged' => 1
        ]);

        // When we create new timecard for next month
        $nextMonthTimecard = factory(Timecard::class)->create(['month' => 4, 'user_id' => $user->id]);

        // Then new timecard should have copy of compensation
        $nextMonthCompensation = $nextMonthTimecard->compensations->first();
        $this->assertEquals(
            [$compensation->name, $compensation->amount, $compensation->prolonged],
            [$nextMonthCompensation->name, $nextMonthCompensation->amount, $nextMonthCompensation->prolonged]
        );
    }

    /** @test */
    public function we_cannot_create_two_timecards_for_one_user_and_same_month_and_year(): void
    {
        // Delete all other timecards
        Timecard::query()->delete();
        // Given user
        $user = factory(User::class)->create();
        // Given timecard for march
        $timecard = factory(Timecard::class)->create(['month' => 3, 'user_id' => $user->id]);

        // When we create new timecard for same month and same user
        $sameMonthTimecard = factory(Timecard::class)->create(['month' => 3, 'user_id' => $user->id]);

        // Then we should have one timecard in system, old one
        $timecardFromDB = Timecard::first();
        $this->assertEquals(1, Timecard::count());
        $this->assertEquals($timecard->id, $timecardFromDB->id);
    }

    /** @test */
    public function we_can_create_two_timecards_for_one_user_and_same_month_in_other_years(): void
    {
        // Delete all other timecards
        Timecard::query()->delete();
        // Given user
        $user = factory(User::class)->create();
        // Given timecard for march
        $timecard = factory(Timecard::class)->create(['month' => 3, 'user_id' => $user->id, 'created_at' => now()->subYear()]);

        // When we create new timecard for same month and same user, but in other year
        $sameMonthTimecard = factory(Timecard::class)->create(['month' => 3, 'user_id' => $user->id]);

        // Then we should have two timecards in system
        $this->assertEquals(2, Timecard::count());
    }

    /** @test */
    public function user_without_permission_cannot_update_timecard_bonuses_list(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with any data
        $data = [];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_bonuses', $timecard->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_update_timecard_bonuses_list_by_creating_new(): void
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'bonuses' => [
                [
                    'name' => 'Test',
                    'amount' => 10.00,
                    'project_id' => $project->id,
                ],
                [
                    'name' => 'Test 2',
                    'amount' => 20.00,
                    'project_id' => $project->id,
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_bonuses', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have two additions
        $this->assertCount(2, $timecard->refresh()->additions);
        $this->assertEquals(collect($data['bonuses'])->pluck('name'), $timecard->additions->pluck('name'));
        $this->assertEquals(collect($data['bonuses'])->pluck('amount'), $timecard->additions->pluck('amount'));
        $this->assertEquals(collect($data['bonuses'])->pluck('project_id'), $timecard->additions->pluck('project_id'));
        $this->assertEquals(TimecardAddition::TYPES_ENG['bonus'], $timecard->additions[0]->type);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Bonuses should have log too
        $bonuse = $timecard->additions->first();
        $this->assertCount(1, $bonuse->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_bonuses_list_by_deleting_all_bonuses(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With bonuses
        $bonuses = factory(TimecardAddition::class, 2)->create(['type' => TimecardAddition::TYPES_ENG['bonus'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'deleted_addition_ids' => $bonuses->pluck('id')->toArray()
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_bonuses', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have zero additions
        $this->assertCount(0, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Additions should have two logs (one about creation and one about deletion)
        $addition = $bonuses->first()->refresh();
        $this->assertCount(2, $addition->logs);
        // Bonuses should be trashed
        $this->assertSoftDeleted($bonuses[0]);
        $this->assertSoftDeleted($bonuses[1]);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_bonuses_list_by_deleting_one_bonus(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With bonuses
        $bonuses = factory(TimecardAddition::class, 2)->create(['type' => TimecardAddition::TYPES_ENG['bonus'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'deleted_addition_ids' => [$bonuses[0]->id]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_bonuses', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one addition
        $this->assertCount(1, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Deleted addition should have two logs (one about creation and one about deletion)
        $addition = $bonuses->first()->refresh();
        $this->assertCount(2, $addition->logs);
        // Bonus should be trashed
        $this->assertSoftDeleted($bonuses[0]);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_bonuses_list_by_updating_them(): void
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With bonuses
        $bonuse = factory(TimecardAddition::class)->create([
            'type' => TimecardAddition::TYPES_ENG['bonus'],
            'timecard_id' => $timecard->id,
            'project_id' => $project->id
        ]);
        // Given new project
        $newProject = factory(Project::class)->create();

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'bonuses' => [
                [
                    'id' => $bonuse->id,
                    'name' => 'TAKE',
                    'amount' => 69.00,
                    'project_id' => $newProject->id,
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_bonuses', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one addition
        $this->assertCount(1, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Updated addition should have two logs (one about creation and one about update)
        $this->assertCount(2, $bonuse->refresh()->logs);
        // Bonus should be updated
        $this->assertEquals([$data['bonuses'][0]['name'], $data['bonuses'][0]['amount'], $data['bonuses'][0]['project_id']], [$bonuse->name, $bonuse->amount, $newProject->id]);
    }

    /** @test */
    public function user_with_permission_cannot_update_timecard_bonuses_if_timelist_is_closed(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);
        // With bonuses
        $bonuse = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['bonus'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'bonuses' => [
                [
                    'id' => $bonuse->id,
                    'name' => 'TAKE',
                    'amount' => 69.00,
                    'project_id' => factory(Project::class)->create()->id,
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_bonuses', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель закрыт, ему нельзя менять список премий');
    }

    /** @test */
    public function timecard_bonus_have_project_relation(): void
    {
        // Given project
        $project = factory(Project::class)->create();
        // Given bonus
        $bonus = factory(TimecardAddition::class)->create([
            'type' => TimecardAddition::TYPES_ENG['bonus'],
            'project_id' => $project->id
        ]);

        // Then bonus should have project() relation
        $this->assertEquals($project->id, $bonus->project->id);
    }

    /** @test */
    public function user_without_permission_cannot_update_timecard_fines_list(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with any data
        $data = [];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_fines', $timecard->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_update_timecard_fines_list_by_creating_new(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'fines' => [
                [
                    'name' => 'Test',
                    'amount' => 10.00,
                ],
                [
                    'name' => 'Test 2',
                    'amount' => 20.00,
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_fines', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have two additions
        $this->assertCount(2, $timecard->refresh()->additions);
        $this->assertEquals(collect($data['fines'])->pluck('name'), $timecard->additions->pluck('name'));
        $this->assertEquals(collect($data['fines'])->pluck('amount'), $timecard->additions->pluck('amount'));
        $this->assertEquals(TimecardAddition::TYPES_ENG['fine'], $timecard->additions[0]->type);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Fines should have log too
        $finee = $timecard->additions->first();
        $this->assertCount(1, $finee->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_fines_list_by_deleting_all_fines(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With fines
        $fines = factory(TimecardAddition::class, 2)->create(['type' => TimecardAddition::TYPES_ENG['fine'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'deleted_addition_ids' => $fines->pluck('id')->toArray()
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_fines', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have zero additions
        $this->assertCount(0, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Additions should have two logs (one about creation and one about deletion)
        $addition = $fines->first()->refresh();
        $this->assertCount(2, $addition->logs);
        // Fines should be trashed
        $this->assertSoftDeleted($fines[0]);
        $this->assertSoftDeleted($fines[1]);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_fines_list_by_deleting_one_fine(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With fines
        $fines = factory(TimecardAddition::class, 2)->create(['type' => TimecardAddition::TYPES_ENG['fine'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'deleted_addition_ids' => [$fines[0]->id]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_fines', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one addition
        $this->assertCount(1, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Deleted addition should have two logs (one about creation and one about deletion)
        $addition = $fines->first()->refresh();
        $this->assertCount(2, $addition->logs);
        // Fines should be trashed
        $this->assertSoftDeleted($fines[0]);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_fines_list_by_updating_them(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given opened timecard
        $timecard = factory(Timecard::class)->create();
        // With fines
        $fine = factory(TimecardAddition::class)->create([
            'type' => TimecardAddition::TYPES_ENG['fine'],
            'timecard_id' => $timecard->id
        ]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'fines' => [
                [
                    'id' => $fine->id,
                    'name' => 'TAKE',
                    'amount' => 69.00,
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_fines', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one addition
        $this->assertCount(1, $timecard->refresh()->additions);
        // One log
        $this->assertCount(1, $timecard->logs);
        // Updated addition should have two logs (one about creation and one about update)
        $this->assertCount(2, $fine->refresh()->logs);
        // Fine should be updated
        $this->assertEquals([$data['fines'][0]['name'], $data['fines'][0]['amount']], [$fine->name, $fine->amount]);
    }

    /** @test */
    public function user_with_permission_cannot_update_timecard_fines_if_timelist_is_closed(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_MANAGE_TIMECARD[array_rand(self::ABLE_TO_MANAGE_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);
        // With fines
        $finee = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['fine'], 'timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_id' => $timecard->id,
            'fines' => [
                [
                    'id' => $finee->id,
                    'name' => 'TAKE',
                    'amount' => 69.00,
                ]
            ]
        ];
        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_fines', $timecard->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель закрыт, ему нельзя менять список штрафов');
    }

    /** @test */
    public function timecard_additions_getter_can_return_nothing_if_no_additions_exist(): void
    {
        // Given no additions
        TimecardAddition::query()->delete();

        // When we use timecard additions getter
        $response = $this->actingAs(factory(User::class)->create())->post(route('human_resources.timecard.get_addition_names'))->json();

        // Then response should be empty
        $this->assertEmpty($response);
    }

    /** @test */
    public function timecard_additions_getter_can_return_everything(): void
    {
        // Given additions
        $additions = factory(TimecardAddition::class, 5)->create();
        factory(TimecardAddition::class)->create(['name' => $additions->first()->name]);

        // When we use timecard additions getter
        $response = $this->actingAs(factory(User::class)->create())->post(route('human_resources.timecard.get_addition_names'))->json();

        // Then response shouldn't be empty
        $this->assertNotEmpty($response);
        $this->assertEquals($additions->pluck('id'), collect($response)->pluck('code'));
    }

    /** @test */
    public function timecard_additions_getter_can_return_additions_by_type(): void
    {
        // Given timecard additions
        $compensation = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['compensation']]);
        $fine = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['fine']]);
        $bonus = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['bonus']]);

        // When we use timecard additions getter
        $compensationsResponse = $this->actingAs(factory(User::class)->create())->post(route('human_resources.timecard.get_addition_names'), ['type' => $compensation->type])->json();
        $finesResponse = $this->actingAs(factory(User::class)->create())->post(route('human_resources.timecard.get_addition_names'), ['type' => $fine->type])->json();
        $bonusesResponse = $this->actingAs(factory(User::class)->create())->post(route('human_resources.timecard.get_addition_names'), ['type' => $bonus->type])->json();

        // Then responses shouldn't be empty
        $this->assertNotEmpty($compensationsResponse);
        $this->assertNotEmpty($finesResponse);
        $this->assertNotEmpty($bonusesResponse);
        $this->assertEquals(collect([$compensation])->pluck('id'), collect($compensationsResponse)->pluck('code'));
        $this->assertEquals(collect([$fine])->pluck('id'), collect($finesResponse)->pluck('code'));
        $this->assertEquals(collect([$bonus])->pluck('id'), collect($bonusesResponse)->pluck('code'));
    }

    /** @test */
    public function timecard_additions_getter_can_return_additions_by_type_and_search(): void
    {
        // Given timecard additions
        $compensation = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['compensation'], 'name' => 'PUPA']);
        $additionalCompensation = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['compensation'], 'name' => '123']);
        $fine = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['fine'], 'name' => 'LUPA']);
        $additionalFine = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['fine'], 'name' => '456']);
        $bonus = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['bonus'], 'name' => 'NENE']);
        $additionalBonus = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['bonus'], 'name' => '789']);

        // When we use timecard additions getter
        $compensationsResponse = $this->actingAs(factory(User::class)->create())->post(
            route('human_resources.timecard.get_addition_names'),
            ['type' => $compensation->type, 'q' => 'PU']
        )->json();
        $finesResponse = $this->actingAs(factory(User::class)->create())->post(
            route('human_resources.timecard.get_addition_names'),
            ['type' => $fine->type, 'q' => 'LU']
        )->json();
        $bonusesResponse = $this->actingAs(factory(User::class)->create())->post(
            route('human_resources.timecard.get_addition_names'),
            ['type' => $bonus->type, 'q' => 'NE']
        )->json();

        // Then responses shouldn't be empty
        $this->assertNotEmpty($compensationsResponse);
        $this->assertNotEmpty($finesResponse);
        $this->assertNotEmpty($bonusesResponse);
        $this->assertEquals(collect([$compensation])->pluck('id'), collect($compensationsResponse)->pluck('code'));
        $this->assertEquals(collect([$fine])->pluck('id'), collect($finesResponse)->pluck('code'));
        $this->assertEquals(collect([$bonus])->pluck('id'), collect($bonusesResponse)->pluck('code'));
    }

    /** @test */
    public function timecard_can_have_days(): void
    {
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $record = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);

        // Then timecard should have days relation with count 1
        $this->assertCount(1, $timecard->refresh()->days);
        $this->assertEquals(collect([$record])->pluck('id'), $timecard->days->pluck('id'));
        // And day should have timecard relation
        $this->assertEquals($timecard->id, $record->timecard->id);
    }

    /** @test */
    public function when_we_create_timecard_it_should_have_days_for_all_days_in_month(): void
    {
        // Given timecard
        $timecard = factory(Timecard::class)->create();

        // Then timecard should have days relation with count equal to days in month
        $daysInMonth = now()->daysInMonth;
        $this->assertCount($daysInMonth, $timecard->days);
    }

    /** @test */
    public function timecard_can_not_have_day_with_day_property_out_of_month_days(): void
    {
        // Given timecard
        $timecard = factory(Timecard::class)->create(['month' => now()->month]);
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $record = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => now()->lastOfMonth()->day + 1]);

        // Then timecard should have zero days
        $this->assertCount(0, $timecard->refresh()->days);
    }

    /** @test */
    public function timecard_day_should_has_user(): void
    {
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        $timecard = factory(Timecard::class)->create(['user_id' => $user->id]);
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);

        // Then timecard day should have user relation to user from timecard
        $this->assertEquals($user->id, $timecardDay->user->id);
    }

    /** @test */
    public function timecard_day_can_have_records(): void
    {
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given day record
        $record = factory(TimecardRecord::class)->create(['timecard_day_id' => $timecardDay->id]);

        // Then timecard day should have records relation with count 1
        $this->assertCount(1, $timecardDay->refresh()->records);
        $this->assertEquals(collect([$record])->pluck('id'), $timecardDay->records->pluck('id'));
        // And record should have timecard day relation
        $this->assertEquals($timecardDay->id, $record->timecardDay->id);
    }

    /** @test */
    public function timecard_day_can_have_records_by_type(): void
    {// Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given timecard day records
        $deal = factory(TimecardRecord::class)->create(['timecard_day_id' => $timecardDay->id, 'type' => TimecardRecord::TYPES_ENG['deals']]);
        $workingHour = factory(TimecardRecord::class)->create(['timecard_day_id' => $timecardDay->id, 'type' => TimecardRecord::TYPES_ENG['working hours']]);
        $project = factory(TimecardRecord::class)->create(['timecard_day_id' => $timecardDay->id, 'type' => TimecardRecord::TYPES_ENG['time periods']]);

        // Then timecard day should have records relation with count 3
        $this->assertCount(3, $timecardDay->refresh()->records);
        $this->assertEquals(collect([$deal, $workingHour, $project])->pluck('id'), $timecardDay->records->pluck('id'));
        // And record should have timecard day relation by type
        $this->assertEquals($timecardDay->deals->first()->id, $deal->id);
        $this->assertEquals($timecardDay->workingHours->first()->id, $workingHour->id);
        $this->assertEquals($timecardDay->timePeriods->first()->id, $project->id);
    }

    /** @test */
    public function user_without_permission_cannot_update_time_period_record_for_timecard_day(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);

        // When user make put request with any data
        $data = ['timecard_day_id' => $timecardDay->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_time_periods_list_by_creating_new_record_with_start_date_only(): void
    {
        /**
         * Let's imagine that some user will be responsible for marking users time periods and he must fill start date
         * In this case he can provide only start time
         */
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'periods' => [
                [
                    'project_id' => $project->id,
                    'start' => '8-00',
                ],
                [
                    'commentary' => 'CUSTOM TIME PERIOD',
                    'start' => '9-00',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        $updated_periods = collect($response->json('data'));
        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard day should have two records
        $this->assertCount(2, $timecardDay->refresh()->records);
        $this->assertEquals($updated_periods->pluck('project_id'), $timecardDay->records->pluck('project_id'));
        $this->assertEquals($updated_periods->pluck('commentary'), $timecardDay->records->pluck('commentary'));
        $this->assertEquals($updated_periods->pluck('start'), $timecardDay->records->pluck('start'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['time periods'], $timecardDay->records[0]->type);
        // Two logs (about creation and about time periods update)
        $this->assertCount(2, $timecardDay->logs);
        // Time periods should have log too
        $period = $timecardDay->records->first();
        $this->assertCount(1, $period->logs);
    }

    /** @test */
    public function user_with_permission_cannot_update_timecard_day_time_periods_list_by_creating_new_record_with_end_date_only(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'periods' => [
                [
                    'project_id' => $project->id,
                    'end' => '8-00',
                ],
                [
                    'commentary' => 'CUSTOM TIME PERIOD',
                    'end' => '9-00',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors();
        $response->assertSessionHasErrors(['periods.*.start']);
    }

    /** @test */
    public function user_with_permission_can_delete_timecard_day_time_periods_by_making_special_request(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();;
        // Given working time period record
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'project_id' => $project->id,
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'deleted_addition_ids' => [$timePeriod->id]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard day should have zero records
        $this->assertCount(0, $timecardDay->refresh()->records);
        // Two logs (about creation and about time periods update)
        $this->assertCount(2, $timecardDay->logs);
        // Hours should have two logs (about creation and deletion)
        $timePeriod->refresh();
        $this->assertCount(2, $timePeriod->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_time_periods_list_by_updating_existed_record_by_adding_end_time(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();
        // Given time period
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'project_id' => $project->id,
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'periods' => [
                [
                    'id' => $timePeriod->id,
                    'project_id' => $timePeriod->project_id,
                    'start' => '8-00',
                    'end' => '17-00',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        $updated_periods = collect($response->json('data'));

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard day should have one record
        $this->assertCount(1, $timecardDay->refresh()->records);
        $this->assertEquals($updated_periods->pluck('project_id'), $timecardDay->records->pluck('project_id'));
        $this->assertEquals($updated_periods->pluck('start'), $timecardDay->records->pluck('start'));
        $this->assertEquals($updated_periods->pluck('end'), $timecardDay->records->pluck('end'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['time periods'], $timecardDay->records[0]->type);
        // Two logs (about creation and about time periods update)
        $this->assertCount(2, $timecardDay->logs);
        // Periods should have two logs (create and update)
        $period = $timecardDay->records->first();
        $this->assertCount(2, $period->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_time_periods_list_by_updating_existed_record_by_changing_time(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();
        // Given time period
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'end' => '15-00',
            'project_id' => $project->id,
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'periods' => [
                [
                    'id' => $timePeriod->id,
                    'project_id' => $timePeriod->project_id,
                    'start' => '13-00',
                    'end' => '17-00',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard day should have one record
        $this->assertCount(1, $timecardDay->refresh()->records);
        $this->assertEquals(collect($data['periods'])->pluck('project_id'), $timecardDay->records->pluck('project_id'));
        $this->assertEquals(collect($data['periods'])->pluck('start'), $timecardDay->records->pluck('start'));
        $this->assertEquals(collect($data['periods'])->pluck('end'), $timecardDay->records->pluck('end'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['time periods'], $timecardDay->records[0]->type);
        // Two logs (about creation and about time periods update)
        $this->assertCount(2, $timecardDay->logs);
        // Periods should have two logs (create and update)
        $period = $timecardDay->records->first();
        $this->assertCount(2, $period->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_time_periods_list_by_updating_existed_record_by_changing_project_to_commentary(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();
        // Given time period
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'end' => '15-00',
            'project_id' => $project->id,
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'periods' => [
                [
                    'id' => $timePeriod->id,
                    'commentary' => 'CUSTOM',
                    'start' => '13-00',
                    'end' => '17-00',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard day should have one record
        $this->assertCount(1, $timecardDay->refresh()->records);
        $this->assertEmpty($timecardDay->records[0]->project_id);
        $this->assertEquals(collect($data['periods'])->pluck('commentary'), $timecardDay->records->pluck('commentary'));
        $this->assertEquals(collect($data['periods'])->pluck('start'), $timecardDay->records->pluck('start'));
        $this->assertEquals(collect($data['periods'])->pluck('end'), $timecardDay->records->pluck('end'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['time periods'], $timecardDay->records[0]->type);
        // Two logs (about creation and about time periods update)
        $this->assertCount(2, $timecardDay->logs);
        // Periods should have two logs (create and update)
        $period = $timecardDay->records->first();
        $this->assertCount(2, $period->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_time_periods_list_by_updating_existed_record_by_changing_commentary_to_project(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();
        // Given time period
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'end' => '15-00',
            'commentary' => 'SHIP',
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'periods' => [
                [
                    'id' => $timePeriod->id,
                    'project_id' => $project->id,
                    'start' => '13-00',
                    'end' => '17-00',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard day should have one record
        $this->assertCount(1, $timecardDay->refresh()->records);
        $this->assertEmpty($timecardDay->records[0]->commentary);
        $this->assertEquals(collect($data['periods'])->pluck('project_id'), $timecardDay->records->pluck('project_id'));
        $this->assertEquals(collect($data['periods'])->pluck('start'), $timecardDay->records->pluck('start'));
        $this->assertEquals(collect($data['periods'])->pluck('end'), $timecardDay->records->pluck('end'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['time periods'], $timecardDay->records[0]->type);
        // Two logs (about creation and about time periods update)
        $this->assertCount(2, $timecardDay->logs);
        // Periods should have two logs (create and update)
        $period = $timecardDay->records->first();
        $this->assertCount(2, $period->logs);
    }

    /** @test */
    public function user_with_permission_can_not_update_timecard_day_time_periods_list_by_updating_existed_record_with_end_date_but_without_start(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given time period record
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'end' => '17-00',
            'commentary' => 'SHIP',
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'periods' => [
                [
                    'id' => $timePeriod->id,
                    'commentary' => $timePeriod->commentary,
                    'end' => $timePeriod->end
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then user should have errors
        $response->assertSessionHasErrors();
        $response->assertSessionHasErrors(['periods.*.start']);
    }

    /** @test */
    public function user_with_permission_can_not_update_timecard_day_time_period_list_if_timecard_is_closed(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'hours' => [
                [
                    'commentary' => 'COMMENTARY',
                    'start' => '8-00',
                    'end' => '17-00',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель закрыт, ему нельзя менять временные промежутки');
    }

    /** @test */
    public function user_without_permission_cannot_update_deal_records_for_timecard(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);

        // When user make put request with any data
        $data = ['timecard_day_id' => $timecardDay->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_deals', $timecardDay->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_deals_list_by_creating_new_record(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deals
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();
        $secondDeal = TariffRates::whereType(2)->inRandomOrder()->first();

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'day' => now()->day,
            'deals' => [
                [
                    'tariff_id' => $deal->id,
                    'length' => '14.5',
                    'amount' => '8',
                ],
                [
                    'tariff_id' => $secondDeal->id,
                    'length' => '69.5',
                    'amount' => '96',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_deals', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard day should have two records
        $this->assertCount(2, $timecardDay->refresh()->records);
        $this->assertEquals(collect($data['deals'])->pluck('tariff_id'), $timecardDay->records->pluck('tariff_id'));
        $this->assertEquals(collect($data['deals'])->pluck('length'), $timecardDay->records->pluck('length'));
        $this->assertEquals(collect($data['deals'])->pluck('amount'), $timecardDay->records->pluck('amount'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['deals'], $timecardDay->records[0]->type);
        // Two logs (about creation and about deals update)
        $this->assertCount(2, $timecardDay->logs);
        // Deals should have log too
        $deal = $timecardDay->records->first();
        $this->assertCount(1, $deal->logs);
    }

    /** @test */
    public function user_with_permission_can_delete_timecard_day_deals_by_making_special_request(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deal
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();
        // Given working deal record
        $dealRecord = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['deals'],
            'tariff_id' => $deal->id,
            'length' => '14.5',
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'deleted_addition_ids' => [$dealRecord->id]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_deals', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have zero records
        $this->assertCount(0, $timecardDay->refresh()->records);
        // Two logs (about creation and about deals update)
        $this->assertCount(2, $timecardDay->logs);
        // Hours should have two logs (about creation and deletion)
        $dealRecord->refresh();
        $this->assertCount(2, $dealRecord->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_deals_list_by_updating_existed_record(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deal
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();
        // Given working deal record
        $dealRecord = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['deals'],
            'tariff_id' => $deal->id,
            'length' => '14.5',
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $secondDeal = TariffRates::whereType(2)->inRandomOrder()->first();
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'deals' => [
                [
                    'id' => $dealRecord->id,
                    'tariff_id' => $secondDeal->id,
                    'length' => '69',
                    'amount' => '2',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_deals', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one record
        $this->assertCount(1, $timecardDay->refresh()->records);
        $this->assertEquals(collect($data['deals'])->pluck('tariff_id'), $timecardDay->records->pluck('tariff_id'));
        $this->assertEquals(collect($data['deals'])->pluck('length'), $timecardDay->records->pluck('length'));
        $this->assertEquals(collect($data['deals'])->pluck('amount'), $timecardDay->records->pluck('amount'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['deals'], $timecardDay->records[0]->type);
        // Two logs (about creation and about deals update)
        $this->assertCount(2, $timecardDay->logs);
        // Deals should have two logs (create and update)
        $deal = $timecardDay->records->first();
        $this->assertCount(2, $deal->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_deals_group(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deal
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();
        // Given working deal record
        $dealRecord = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['deals'],
            'tariff_id' => $deal->id,
            'length' => '14.5',
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $secondDeal = TariffRates::whereType(2)->inRandomOrder()->first();
        $data = [
            'old_tariff' => $dealRecord->tariff_id,
            'new_tariff' => $secondDeal->id,
            'old_length' => $dealRecord->length,
            'new_length' => null,
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard.update_deals_group', $timecard->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one record
        $this->assertCount(1, $timecard->refresh()->records);
        $this->assertEquals($secondDeal->id, $timecard->records->pluck('tariff_id')->first());
        $this->assertEquals($dealRecord->length, $timecard->records->pluck('length')->first());
        $this->assertEquals($dealRecord->amount, $timecard->records->pluck('amount')->first());
        $this->assertEquals(TimecardRecord::TYPES_ENG['deals'], $timecard->records[0]->type);
        // Two logs (about creation and about deals update)
        $this->assertCount(1, $timecard->logs);
        // Deals should have two logs (create and update)
        $deal = $timecard->records->first();
        $this->assertCount(1, $deal->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_deals_group_in_daily_report(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        $project = Project::first();
        $project->users()->attach($user->id);
        // Given timecard
        $timecard = factory(Timecard::class)->create(['user_id' => $user->id]);
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deal
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();
        // Given working deal record
        $dealRecord = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['deals'],
            'tariff_id' => $deal->id,
            'length' => '14.5',
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);
$this->withoutExceptionHandling();
        // When user make put request with data
        $secondDeal = TariffRates::whereType(2)->inRandomOrder()->first();
        $data = [
            'day' => "$timecardDay->day.$timecard->month.$timecard->year",
            'old_tariff' => $dealRecord->tariff_id,
            'new_tariff' => $secondDeal->id,
            'old_length' => $dealRecord->length,
            'new_length' => null,
            'project_id' => $project->id,
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_day_deals_group'), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one record
        $this->assertCount(1, $timecard->refresh()->records);
        $this->assertEquals($secondDeal->id, $timecard->records->pluck('tariff_id')->first());
        $this->assertEquals($dealRecord->length, $timecard->records->pluck('length')->first());
        $this->assertEquals($dealRecord->amount, $timecard->records->pluck('amount')->first());
        $this->assertEquals(TimecardRecord::TYPES_ENG['deals'], $timecard->records[0]->type);
        // Two logs (about creation and about deals update)
        $this->assertCount(1, $timecard->logs);
        // Deals should have two logs (create and update)
        $deal = $timecard->records->first();
        $this->assertCount(1, $deal->logs);
    }


    /** @test */
    public function user_with_permission_can_not_update_timecard_day_deals_list_if_timecard_is_closed(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deal
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'deals' => [
                [
                    'tariff_id' => $deal->id,
                    'length' => '14.5',
                    'amount' => '8',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_deals', $timecardDay->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель закрыт, ему нельзя менять сделки');
    }

    /** @test */
    public function user_without_permission_cannot_update_working_hour_records_for_timecard_day(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);

        // When user make put request with any data
        $data = ['timecard_day_id' => $timecardDay->id];
        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_working_hours', $timecardDay->id), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_working_hours_list_by_creating_new_record(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given tariffs
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $secondTariff = TariffRates::whereType(1)->inRandomOrder()->first();
        // Given project
        $project = factory(Project::class)->create();

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'day' => now()->day,
            'working_hours' => [
                [
                    'tariff_id' => $tariff->id,
                    'amount' => '8',
                    'project_id' => $project->id,
                ],
                [
                    'tariff_id' => $secondTariff->id,
                    'amount' => '96',
                    'project_id' => $project->id,
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_working_hours', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have two records
        $this->assertCount(2, $timecardDay->refresh()->records);
        $this->assertEquals(collect($data['working_hours'])->pluck('tariff_id'), $timecardDay->records->pluck('tariff_id'));
        $this->assertEquals(collect($data['working_hours'])->pluck('amount'), $timecardDay->records->pluck('amount'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['working hours'], $timecardDay->records[0]->type);
        // Two logs (about creation and about deals update)
        $this->assertCount(2, $timecardDay->logs);
        // Deals should have log too
        $tariff = $timecardDay->records->first();
        $this->assertCount(1, $tariff->logs);
    }

    /** @test */
    public function user_with_permission_can_delete_timecard_day_working_hours_by_making_special_request(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given tariff
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        // Given working working_hour record
        $workingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'day' => now()->day,
            'deleted_addition_ids' => [$workingHour->id]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_working_hours', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have zero records
        $this->assertCount(0, $timecardDay->refresh()->records);
        // Two logs (about creation and about deals update)
        $this->assertCount(2, $timecardDay->logs);
        // Hours should have two logs (about creation and deletion)
        $workingHour->refresh();
        $this->assertCount(2, $workingHour->logs);
    }

    /** @test */
    public function user_with_permission_can_update_timecard_day_working_hours_list_by_updating_existed_record(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given tariff
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        // Given working hour record
        $workingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);
        // Given project
        $project = factory(Project::class)->create();

        // When user make put request with data
        $secondDeal = TariffRates::whereType(1)->inRandomOrder()->first();
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'day' => now()->day,
            'working_hours' => [
                [
                    'id' => $workingHour->id,
                    'tariff_id' => $secondDeal->id,
                    'amount' => '2',
                    'project_id' => $project->id,
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_working_hours', $timecardDay->id), $data);

        // Then everything should be OK
        $response->assertSessionDoesntHaveErrors();
        // Timecard should have one record
        $this->assertCount(1, $timecardDay->refresh()->records);
        $this->assertEquals(collect($data['working_hours'])->pluck('tariff_id'), $timecardDay->records->pluck('tariff_id'));
        $this->assertEquals(collect($data['working_hours'])->pluck('amount'), $timecardDay->records->pluck('amount'));
        $this->assertEquals(TimecardRecord::TYPES_ENG['working hours'], $timecardDay->records[0]->type);
        // Two logs (about creation and about deals update)
        $this->assertCount(2, $timecardDay->logs);
        // Deals should have two logs (create and update)
        $tariff = $timecardDay->records->first();
        $this->assertCount(2, $tariff->logs);
    }

    /** @test */
    public function user_with_permission_can_not_update_timecard_day_working_hours_list_if_timecard_is_closed(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given closed timecard
        $timecard = factory(Timecard::class)->create(['is_opened' => 0]);
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given tariff
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'working_hours' => [
                [
                    'tariff_id' => $tariff->id,
                    'amount' => '8',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_working_hours', $timecardDay->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('already_closed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('already_closed')[0], 'Данный табель закрыт, ему нельзя менять рабочее время');
    }

    /** @test */
    public function user_with_permission_can_not_change_timecard_day_working_hours_record_type(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given tariff
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        // Given working hour record
        $workingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);
        // Given project
        $project = factory(Project::class)->create();

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'day' => now()->day,
            'working_hours' => [
                [
                    'type' => TimecardRecord::TYPES_ENG['deals'],
                    'id' => $workingHour->id,
                    'tariff_id' => $workingHour->tariff_id,
                    'amount' => '2',
                    'project_id' => $project->id,
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_working_hours', $timecardDay->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('working_hours.*.type');
    }

    /** @test */
    public function user_with_permission_can_not_change_timecard_day_deals_record_type(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deal
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();
        // Given working hour record
        $dealRecord = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['deals'],
            'tariff_id' => $deal->id,
            'length' => '14.5',
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'deals' => [
                [
                    'type' => TimecardRecord::TYPES_ENG['working hours'],
                    'id' => $dealRecord->id,
                    'tariff_id' => $dealRecord->tariff_id,
                    'length' => '14.5',
                    'amount' => '8',
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_deals', $timecardDay->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('deals.*.type');
    }

    /** @test */
    public function user_with_permission_can_not_change_timecard_day_time_periods_record_type(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();
        // Given time period
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'end' => '17-00',
            'project_id' => $project->id,
            'timecard_day_id' => $timecardDay->id,
        ]);

        // When user make put request with data
        $data = [
            'timecard_day_id' => $timecardDay->id,
            'day' => now()->day,
            'periods' => [
                [
                    'id' => $timePeriod->id,
                    'project_id' => $timePeriod->project_id,
                    'start' => '8-00',
                    'end' => '17-00',
                    'type' => TimecardRecord::TYPES_ENG['deals'],
                ],
            ]
        ];

        $response = $this->actingAs($user)->put(route('human_resources.timecard_day.update_time_periods', $timecardDay->id), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('periods.*.type');
    }

    /** @test */
    public function appearance_control_task_solving_logic_good_scenario(): void
    {
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);

        // When user create timecard day time periods for each user
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'task_id' => $task->id,
            'periods' => [
                [
                    'project_id' => $project->id,
                    'start' => '8-00',
                ],
            ]
        ];
        $data2 = [
            'timecard_day_id' => $secondTimecardDay->id,
            'task_id' => $task->id,
            'periods' => [
                [
                    'project_id' => $project->id,
                    'start' => '8-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);
        $response2 = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $secondTimecardDay->id), $data2);

        // Then task should be solved
        $this->assertEquals(1, $task->refresh()->is_solved);
    }

    /** @test */
    public function appearance_control_task_solving_logic_bad_scenario(): void
    {
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);

        // When we execute command without parameters (this equals to 8 AM)
        $call = $this->artisan('appearance:control')->run();
        // Project should have new task with 40 status
        $tasks = $project->tasks()->where('status', 40)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);

        // When user create timecard day time periods for one user
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'task_id' => $task->id,
            'periods' => [
                [
                    'project_id' => $project->id,
                    'start' => '8-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);

        // Then task shouldn't be solved
        $this->assertEquals(0, $task->refresh()->is_solved);
    }

    /** @test */
    public function time_control_task_solving_logic_good_scenario_with_working_hours(): void
    {
        // What we'll have here: time responsible user completed morning task (equals to workers have time periods),
        // and now he'll have new task. Here we'll describe good scenario:
        // all workers must have closed time periods and they should have working hours, but max amount of working hours per
        // worker on project should be less than sum of hours in time periods on same project (SICK)
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);
        // Given time periods for workers
        $firstTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $firstTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $secondTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);

        // When we execute command without parameters (this equals to 5 PM)
        $call = $this->artisan('work-time:control')->run();
        // Project should have new task with 41 status
        $tasks = $project->tasks()->where('status', 41)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // When user update timecard day time periods for each user
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'periods' => [
                [
                    'id' => $firstTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);
        $data2 = [
            'timecard_day_id' => $secondTimecardDay->id,
            'periods' => [
                [
                    'id' => $secondTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response2 = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $secondTimecardDay->id), $data2);
        // Add working hours for workers (with normal amount) => (18 - 8 - 1 (lunch) = 9, 8 < 9 -> everything ok) #quickMath
        // Given tariff
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $secondTariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $firstWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $firstTimecardDay->id,
            'project_id' => $project->id,
        ]);
        $secondWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $secondTimecardDay->id,
            'project_id' => $project->id,
        ]);

        // When time responsible user press special button, post request fire
        $data3 = ['project_id' => $project->id, 'task_id' => $task->id];
        $response3 = $this->actingAs($timeResponsibleUser)->post(route('human_resources.timecard_day.solve_working_time_task'), $data3);

        // Then task should be solved
        $this->assertEquals(1, $task->refresh()->is_solved);
    }

    /** @test */
    public function time_control_task_solving_logic_bad_scenario_without_closed_time_periods_and_without_working_hours(): void
    {
        // What we'll have here: time responsible user completed morning task (equals to workers have time periods),
        // and now he'll have new task. Here we'll describe bad scenario:
        // all workers don't have closed time periods (that's not okay) and they don't have any working hours (not okay too)
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);
        // Given time periods for workers
        $firstTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $firstTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $secondTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);

        // When we execute command without parameters (this equals to 5 PM)
        $call = $this->artisan('work-time:control')->run();
        // Project should have new task with 41 status
        $tasks = $project->tasks()->where('status', 41)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // When user update timecard day time periods for one user only
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'periods' => [
                [
                    'id' => $firstTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);

        // When time responsible user press special button, post request fire
        $data3 = ['project_id' => $project->id, 'task_id' => $task->id];
        $response3 = $this->actingAs($timeResponsibleUser)->post(route('human_resources.timecard_day.solve_working_time_task'), $data3);

        // Then task shouldn't be solved
        $this->assertEquals(0, $task->refresh()->is_solved);
        // And user should have errors in session
        $response->assertSessionHasErrors('not_completed');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('not_completed')[0], 'Необходимо указать всем сотрудникам временные периоды с началом и концом');
    }

    /** @test */
    public function time_control_task_solving_logic_bad_scenario_without_working_hours(): void
    {
        // What we have here: time responsible user completed morning task (equals to workers have time periods),
        // and now he'll have new task. Here we'll describe good scenario:
        // all workers must have closed time periods and they should have working hours (but not now)
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);
        // Given time periods for workers
        $firstTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $firstTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $secondTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);

        // When we execute command without parameters (this equals to 5 PM)
        $call = $this->artisan('work-time:control')->run();
        // Project should have new task with 41 status
        $tasks = $project->tasks()->where('status', 41)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // When user update timecard day time periods for each user
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'periods' => [
                [
                    'id' => $firstTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);
        $data2 = [
            'timecard_day_id' => $secondTimecardDay->id,
            'periods' => [
                [
                    'id' => $secondTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response2 = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $secondTimecardDay->id), $data2);

        // When time responsible user press special button, post request fire
        $data3 = ['project_id' => $project->id, 'task_id' => $task->id];
        $response3 = $this->actingAs($timeResponsibleUser)->post(route('human_resources.timecard_day.solve_working_time_task'), $data3);

        // Then task shouldn't be solved because workers don't have working hours
        $this->assertEquals(0, $task->refresh()->is_solved);
        // And user should have errors in session
        $response->assertSessionHasErrors('must_fill_hours');
        // With message
        $this->assertEquals(session()->get('errors')->default->get('must_fill_hours')[0], 'Каждый сотрудник, имеющий временной промежуток, обязательно должен иметь тарифы');
    }

    /** @test */
    public function time_control_task_solving_logic_bad_scenario_with_ime_periods_without_working_hours_on_same_project(): void
    {
        // What we'll have here: time responsible user completed morning task (equals to workers have time periods),
        // and now he'll have new task. Here we'll describe good scenario:
        // all workers have closed time periods and they should have working hours on same project (not here)
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);
        // Given time periods for workers
        $firstTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $firstTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $secondTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);

        // When we execute command without parameters (this equals to 5 PM)
        $call = $this->artisan('work-time:control')->run();
        // Project should have new task with 41 status
        $tasks = $project->tasks()->where('status', 41)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // When user update timecard day time periods for each user
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'periods' => [
                [
                    'id' => $firstTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);
        $data2 = [
            'timecard_day_id' => $secondTimecardDay->id,
            'periods' => [
                [
                    'id' => $secondTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response2 = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $secondTimecardDay->id), $data2);
        // Add working hours for workers not on same project
        // Given tariffs
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $secondTariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $secondProject = factory(Project::class)->create();
        $firstWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '11', // TOO MUCH
            'timecard_day_id' => $firstTimecardDay->id,
            'project_id' => $secondProject->id,
        ]);
        $secondWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $secondTimecardDay->id,
            'project_id' => $secondProject->id,
        ]);

        // When time responsible user press special button, post request fire
        $data3 = ['project_id' => $project->id, 'task_id' => $task->id];
        $response3 = $this->actingAs($timeResponsibleUser)->post(route('human_resources.timecard_day.solve_working_time_task'), $data3);

        // Then task shouldn't be solved
        $this->assertEquals(0, $task->refresh()->is_solved);
        // And user should have errors in session
        $response->assertSessionHasErrors('missing_working_hours');
        // With message
        $this->assertEquals(
            session()->get('errors')->default->get('missing_working_hours')[0],
            'У каждого временного промежутка, имеющего привязку к проекту, должен быть тариф на этом же проекте');
    }

    /** @test */
    public function time_control_task_solving_logic_bad_scenario_with_working_hours(): void
    {
        // What we'll have here: time responsible user completed morning task (equals to workers have time periods),
        // and now he'll have new task. Here we'll describe bad scenario:
        // all workers have closed time periods and they have working hours, but max amount of working hours per
        // worker on project should be less than sum of hours in time periods on same project (SICK)
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);
        // Given time periods for workers
        $firstTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $firstTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $secondTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);

        // When we execute command without parameters (this equals to 5 PM)
        $call = $this->artisan('work-time:control')->run();
        // Project should have new task with 41 status
        $tasks = $project->tasks()->where('status', 41)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // When user update timecard day time periods for each user
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'periods' => [
                [
                    'id' => $firstTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);
        $data2 = [
            'timecard_day_id' => $secondTimecardDay->id,
            'periods' => [
                [
                    'id' => $secondTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response2 = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $secondTimecardDay->id), $data2);
        // Add working hours for workers (with not normal amount) => (18 - 8 - 1 (lunch) = 9, 10 > 9 -> not ok) #quickMath
        // Given tariff
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $secondTariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $firstWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '11', // TOO MUCh
            'timecard_day_id' => $firstTimecardDay->id,
            'project_id' => $project->id,
        ]);
        $secondWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $secondTimecardDay->id,
            'project_id' => $project->id,
        ]);

        // When time responsible user press special button, post request fire
        $data3 = ['project_id' => $project->id, 'task_id' => $task->id];
        $response3 = $this->actingAs($timeResponsibleUser)->post(route('human_resources.timecard_day.solve_working_time_task'), $data3);

        // Then task shouldn't be solved
        $this->assertEquals(0, $task->refresh()->is_solved);
        // And user should have errors in session
        $response->assertSessionHasErrors('too_much');
        // With message
        $this->assertEquals(
            session()->get('errors')->default->get('too_much')[0],
            'Сотрудник не может иметь рабочих часов на проекте больше, чем он проработал на нём за день. Если вы уверены в правильности данных, вы можете их подтвердить');
    }

    /** @test */
    public function time_control_task_solving_logic_bad_scenario_with_approval(): void
    {
        // What we'll have here: time responsible user completed morning task (equals to workers have time periods),
        // and now he'll have new task. Here we'll describe bad scenario:
        // all workers have closed time periods and they have working hours, but max amount of working hours per
        // worker on project should be less than sum of hours in time periods on same project (SICK)
        // But then he will click approve button, task should be closed and some users should have some notifications
        // Given time responsible user
        $timeResponsibleUser = factory(User::class)->create(['group_id' => self::ABLE_TO_FILL_TIMECARD[array_rand(self::ABLE_TO_FILL_TIMECARD)]]);
        // Given project with time responsible user
        $project = factory(Project::class)->create(['time_responsible_user_id' => $timeResponsibleUser->id]);
        // Given contract
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'status' => 6]);
        // Given workers
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        // Given tongue RP
        $rpTongue = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpTongue->id, 'role' => 6]);
        // Given pile RP
        $rpPile = factory(User::class)->create();
        $role = ProjectResponsibleUser::create(['project_id' => $project->id, 'user_id' => $rpPile->id, 'role' => 5]);
        // Given timecards
        $date = now();
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);
        // Given time periods for workers
        $firstTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $firstTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'timecard_day_id' => $secondTimecardDay->id,
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'project_id' => $project->id,
            'start' => '8-00',
        ]);

        // When we execute command without parameters (this equals to 5 PM)
        $call = $this->artisan('work-time:control')->run();
        // Project should have new task with 41 status
        $tasks = $project->tasks()->where('status', 41)->get();
        $this->assertCount(1, $tasks);
        $task = $tasks->first();

        // When user update timecard day time periods for each user
        $data = [
            'timecard_day_id' => $firstTimecardDay->id,
            'periods' => [
                [
                    'id' => $firstTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $firstTimecardDay->id), $data);
        $data2 = [
            'timecard_day_id' => $secondTimecardDay->id,
            'periods' => [
                [
                    'id' => $secondTimePeriod->id,
                    'project_id' => $project->id,
                    'start' => '8-00',
                    'end' => '18-00',
                ],
            ]
        ];
        $response2 = $this->actingAs($timeResponsibleUser)->put(route('human_resources.timecard_day.update_time_periods', $secondTimecardDay->id), $data2);
        // Add working hours for workers (with not normal amount) => (18 - 8 - 1 (lunch) = 9, 10 > 9 -> not ok) #quickMath
        // Given tariff
        $tariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $secondTariff = TariffRates::whereType(1)->inRandomOrder()->first();
        $firstWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '11', // TOO MUCh
            'timecard_day_id' => $firstTimecardDay->id,
            'project_id' => $project->id,
        ]);
        $secondWorkingHour = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['working hours'],
            'tariff_id' => $tariff->id,
            'amount' => '8',
            'timecard_day_id' => $secondTimecardDay->id,
            'project_id' => $project->id,
        ]);

        // When time responsible user press special button, post request fire
        $data3 = ['project_id' => $project->id, 'task_id' => $task->id];
        $response3 = $this->actingAs($timeResponsibleUser)->post(route('human_resources.timecard_day.solve_working_time_task'), $data3);

        // Then task shouldn't be solved
        $this->assertEquals(0, $task->refresh()->is_solved);
        // And user should have errors in session
        $response->assertSessionHasErrors('too_much');
        // With message
        $this->assertEquals(
            session()->get('errors')->default->get('too_much')[0],
            'Сотрудник не может иметь рабочих часов на проекте больше, чем он проработал на нём за день. Если вы уверены в правильности данных, вы можете их подтвердить');

        // But then user approve changes
        $data3 = ['project_id' => $project->id, 'task_id' => $task->id, 'approve' => 1];
        $response3 = $this->actingAs($timeResponsibleUser)->post(route('human_resources.timecard_day.solve_working_time_task'), $data3);
        // Task should be solved
        $this->assertEquals(1, $task->refresh()->is_solved);
        // Some notifications should be generated for project RPs
        $notifications = Notification::where('type', 108)->get();
        $this->assertEquals([$rpTongue->id, $rpPile->id], $notifications->pluck('user_id')->toArray());
        // With some text and type
        $this->assertEquals($notifications->first()->name, "В задаче «{$task->name}» пользователя {$task->responsible_user->full_name} возможно заполнение суточного табеля с превышением по ставкам");
    }

    /** @test */
    public function user_without_permission_cannot_generate_work_time_report(): void
    {
        // Given user without permission
        $user = factory(User::class)->create(['group_id' => 30]);

        // When user make post request with any data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.work_time_report'), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_will_have_error_if_he_doesnt_send_any_data(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_GENERATE_REPORT[array_rand(self::ABLE_TO_GENERATE_REPORT)]]);

        // When user make post request without data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.work_time_report'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('project_id');
    }

    /** @test */
    public function user_with_permission_will_have_empty_response_if_he_does_not_send_date_in_good_format(): void
    {
        // GOOD FORMAT - Y-m-d|Y-m-d or Y-m
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => self::ABLE_TO_GENERATE_REPORT[array_rand(self::ABLE_TO_GENERATE_REPORT)]]);
        // Given project
        $project = factory(Project::class)->create();

        // When user make post request with data
        $data = ['project_id' => $project->id, 'date' => now()->startOfMonth()->format('Y.m.d')];
        $response = $this->actingAs($user)->post(route('human_resources.work_time_report'), $data)->json();

        // Then user should have empty response
        $this->assertEmpty($response);
    }
}

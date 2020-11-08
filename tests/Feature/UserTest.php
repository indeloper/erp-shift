<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        User::query()->delete();
    }

    /** @test */
    public function user_who_have_birthday_today_scope_must_return_users_who_have_birthday_today()
    {
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

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
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subMonth()->subYear()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subMonth()->subYears(2)->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we use whoHaveBirthdayToday() scope
        $result = User::whoHaveBirthdayToday()->get();

        // Then $result should contains nothing
        $this->assertEmpty($result);
    }

    /** @test */
    public function user_who_have_birthday_next_week_scope_must_return_users_who_have_birthday_next_week()
    {
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->addWeek()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->addWeek()->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

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
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subMonth()->subYear()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subMonth()->subYears(2)->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we use whoHaveBirthdayNextWeek() scope
        $result = User::whoHaveBirthdayNextWeek()->get();

        // Then $result should contains nothing
        $this->assertEmpty($result);
    }
}

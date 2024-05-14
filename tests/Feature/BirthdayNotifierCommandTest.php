<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BirthdayNotifierCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        User::query()->delete();
        Notification::query()->delete();
    }

    /** @test */
    public function when_we_call_command_some_notifications_should_generate_today_birthday()
    {
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we call notify:send command
        $this->artisan('birthday:check');

        // Then ...
        // Some notifications for $user3 should be generated
        $notifications = Notification::get();
        $this->assertEquals([$user3->id, $user3->id], $notifications->pluck('user_id')->toArray());
        $this->assertEquals([89, 89], $notifications->pluck('type')->toArray());
        $this->assertCount(2, $notifications);
    }

    /** @test */
    public function when_we_call_command_some_notifications_should_generate_next_week_birthday()
    {
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->addWeek()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->addWeek()->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we call notify:send command
        $this->artisan('birthday:check');

        // Then ...
        // Some notifications for $user3 should be generated
        $notifications = Notification::get();
        $this->assertEquals([$user3->id, $user3->id], $notifications->pluck('user_id')->toArray());
        $this->assertEquals([88, 88], $notifications->pluck('type')->toArray());
        $this->assertCount(2, $notifications);
    }

    /** @test */
    public function when_we_call_command_some_notifications_should_generate_mixed()
    {
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->addWeek()->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we call notify:send command
        $this->artisan('birthday:check');

        // Then ...
        // Some notifications for $user3 should be generated
        $notifications = Notification::get();
        $user_ids = [$user2->id, $user3->id, $user3->id, $user1->id];
        sort($user_ids);
        $this->assertEquals($user_ids, $notifications->pluck('user_id')->sort()->values()->toArray());
        $this->assertEquals([89, 89, 88, 88], $notifications->pluck('type')->toArray());
        $this->assertCount(4, $notifications);
    }
}

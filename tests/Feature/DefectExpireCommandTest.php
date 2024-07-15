<?php

namespace Tests\Feature;

use App\Models\TechAcc\Defects\Defects;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DefectExpireCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected $defect1;

    protected $defect2;

    protected $defect3;

    protected $defect4;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Given user
        $this->user = User::whereIn('group_id', [46, 47, 48])->where('is_deleted', 0)->first() ?? User::factory()->create(['group_id' => 47]);
        // Given four defects
        // One that will expire soon
        $this->defect1 = Defects::factory()->create(['responsible_user_id' => $this->user->id, 'status' => 3, 'repair_start_date' => now()->subDay(), 'repair_end_date' => now()]);
        // Second with normal dates
        $this->defect2 = Defects::factory()->create(['responsible_user_id' => $this->user->id, 'status' => 3, 'repair_start_date' => now()->subDay(), 'repair_end_date' => now()->addDays(1)]);
        // Third is closed
        $this->defect3 = Defects::factory()->create(['responsible_user_id' => $this->user->id, 'status' => 4, 'repair_start_date' => now()->subDay(), 'repair_end_date' => now()->addDays(2)]);
        // Fourth is in diagnosis
        $this->defect4 = Defects::factory()->create(['responsible_user_id' => $this->user->id, 'status' => 2]);
    }

    /** @test */
    public function if_we_run_command_some_actions_should_happens(): void
    {
        // When we call notify:send command
        $this->artisan('defects:check');

        // Then ...
        // For defect1 should generate two notifications
        $notifications = $this->defect1->refresh()->notifications;
        $this->assertCount(2, $notifications);
        // This notifications should have 78 type
        $this->assertEquals(78, $notifications->pluck('type')->toArray()[0]);
    }
}

<?php

namespace Tests\Feature\Tech_accounting\FuelTank;

use App\Models\Group;
use App\Models\TechAcc\FuelTank\FuelOperationsHistory;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Models\User;
use Tests\TestCase;

class FuelOperationHistoryTest extends TestCase
{
    /** @test */
    public function it_stores_curr_and_prev_values_of_a_changed_field(): void
    {
        $this->actingAs(User::whereIn('group_id', Group::PROJECT_MANAGERS)->first());
        $operation = FuelTankOperation::factory()->outgo()->create(['value' => 10]);

        $operation->value = 1;
        $operation->save();

        $expected_json = [
            'old_values' => [
                'value' => 10,
            ],
            'new_values' => [
                'value' => 1,
            ],
        ];

        $this->assertEquals($expected_json, FuelOperationsHistory::latest()->first()->changed_fields_parsed);
    }
}

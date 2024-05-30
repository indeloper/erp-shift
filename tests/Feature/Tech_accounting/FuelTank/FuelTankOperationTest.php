<?php

namespace Tests\Feature\Tech_accounting\FuelTank;

use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class FuelTankOperationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::whereIn('group_id', Group::PROJECT_MANAGERS)->first());
    }

    /** @test */
    public function it_can_have_object(): void
    {
        $object = ProjectObject::first();
        $operation = FuelTankOperation::factory()->create(['object_id' => $object->id]);

        $this->assertEquals($object->id, $operation->object->id);
    }

    /** @test */
    public function it_returns_related_tank(): void
    {
        $tanks = FuelTank::factory()->count(20)->create();
        $fuel_operation = FuelTankOperation::factory()->create(['fuel_tank_id' => $tanks[4]->id]);

        $this->assertEquals($fuel_operation->fuel_tank->id, $tanks[4]->id);
    }

    /** @test */
    public function it_calculate_result_value_correctly(): void
    {
        $this->actingAs(User::first());

        $tank = FuelTank::factory()->create();

        $operation_income = FuelTankOperation::factory()->income()->create(['fuel_tank_id' => $tank->id]);
        $this->assertEquals($operation_income->result_value, $tank->fuel_level + $operation_income->value);

        $operation_outgo = FuelTankOperation::factory()->outgo()->create(['fuel_tank_id' => $tank->id]);
        $this->assertEquals($operation_outgo->result_value, $tank->fuel_level - $operation_outgo->value + $operation_income->value);

        $tank->refresh();
        $this->assertEquals($tank->fuel_level, $operation_outgo->result_value);
    }

    /** @test */
    public function it_recalc_tank_fuel_level_on_update(): void
    {
        $this->actingAs(User::first());
        $tank = FuelTank::factory()->create(['fuel_level' => 1000]);
        $operation_income = FuelTankOperation::factory()->outgo()->create([
            'fuel_tank_id' => $tank->id,
            'value' => 100,
        ]);

        $operation_income->value = 50;
        $operation_income->save();

        $tank->refresh();

        $this->assertEquals($tank->fuel_level, 950);
    }

    /** @test */
    public function it_recalc_tank_fuel_level_on_tank_update(): void
    {
        $this->actingAs(User::first());
        $tanks = FuelTank::factory()->count(2)->create(['fuel_level' => 1000]);
        $operation_income = FuelTankOperation::factory()->outgo()->create([
            'fuel_tank_id' => $tanks[0]->id,
            'value' => 100,
        ]);

        $operation_income->fuel_tank_id = $tanks[1]->id;
        $operation_income->save();

        $tanks[0]->refresh();
        $tanks[1]->refresh();

        $this->assertEquals($tanks[0]->fuel_level, 1000);
        $this->assertEquals($tanks[1]->fuel_level, 900);
    }

    /** @test */
    public function it_recalc_tank_fuel_level_on_complicated_update(): void
    {
        $this->actingAs(User::first());
        $tanks = FuelTank::factory()->count(2)->create(['fuel_level' => 1000]);
        $operation_income = FuelTankOperation::factory()->outgo()->create([
            'fuel_tank_id' => $tanks[0]->id,
            'value' => 100,
        ]);

        $operation_income->value = 50;
        $operation_income->fuel_tank_id = $tanks[1]->id;
        $operation_income->save();

        $tanks[0]->refresh();
        $tanks[1]->refresh();

        $this->assertEquals($tanks[0]->fuel_level, 1000);
        $this->assertEquals($tanks[1]->fuel_level, 950);
    }

    /** @test */
    public function it_rollback_tank_fuel_level_on_deleting(): void
    {
        $this->actingAs(User::first());
        $tank = FuelTank::factory()->create(['fuel_level' => 1000]);
        $operation_income = FuelTankOperation::factory()->outgo()->create([
            'fuel_tank_id' => $tank->id,
            'value' => 100,
        ]);

        $operation_income->delete();

        $tank->refresh();

        $this->assertEquals($tank->fuel_level, 1000);
    }

    /** @test */
    public function it_recalc_all_values_on_restore(): void
    {
        $this->actingAs(User::first());
        $tank = FuelTank::factory()->create(['fuel_level' => 1000]);
        $operation_income = FuelTankOperation::factory()->outgo()->create([
            'fuel_tank_id' => $tank->id,
            'value' => 100,
        ]);

        $operation_income->delete();

        $tank->fuel_level = 500;
        $tank->save();

        $operation_income->restore();

        $tank->refresh();
        $operation_income->refresh();

        $this->assertEquals($tank->fuel_level, 400);
        $this->assertEquals($tank->fuel_level, $operation_income->result_value);
        $this->assertEquals($tank->fuel_level, $operation_income->fuel_tank->fuel_level);
    }

    /** @test */
    public function it_return_future_operations(): void
    {
        $tank = FuelTank::factory()->create(['fuel_level' => 1000]);

        $operation_now = FuelTankOperation::factory()->create(['fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->create(['operation_date' => Carbon::now()->addMinute(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->create(['operation_date' => Carbon::now()->addDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->create(['operation_date' => Carbon::now()->addWeek(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->create(['operation_date' => Carbon::now()->subMinute(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->create(['operation_date' => Carbon::now()->subDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);

        $this->assertCount(3, $operation_now->future_history);
    }

    /** @test */
    public function it_recalc_future_operations(): void
    {
        $tank = FuelTank::factory()->create(['fuel_level' => 1000]);

        $operation_now = FuelTankOperation::factory()->income()->create(['fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addMinute(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        $future_operation = FuelTankOperation::factory()->income()->create(['operation_date' => Carbon::now()->addDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->income()->create(['operation_date' => Carbon::now()->subMinute(), 'fuel_tank_id' => $tank->id, 'value' => 10]);

        $old_result = $operation_now->fuel_tank()->first()->fuel_level;

        $operation_now->value += 10;
        $operation_now->save();

        $future_operation->refresh();

        $this->assertEquals($old_result + 10, $future_operation->result_value);
    }

    /** @test */
    public function it_forbids_to_create_big_value_outgo(): void
    {
        $this->actingAs(User::first());
        $tank = FuelTank::factory()->create(['fuel_level' => 100]);

        FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->subDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);

        $tank->refresh();
        $this->assertEquals($tank->fuel_level, 80);

        try {
            $operation_income = FuelTankOperation::factory()->outgo()->create([
                'operation_date' => Carbon::now(),
                'fuel_tank_id' => $tank->id,
                'value' => 1000,
            ]);
        } catch (\Throwable $e) {
        }

        $tank->refresh();
        $this->assertEquals(80, $tank->fuel_level);
    }

    /** @test */
    public function it_forbids_to_change_date_when_it_leads_to_conflict(): void
    {
        $this->actingAs(User::first());
        $tank = FuelTank::factory()->create(['fuel_level' => 100]);
        FuelTankOperation::factory()->income()->create(['operation_date' => Carbon::now()->addDays(3), 'fuel_tank_id' => $tank->id, 'value' => 200]);
        $updated_operation = FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addDays(5), 'fuel_tank_id' => $tank->id, 'value' => 150]);

        $tank->refresh();
        $this->assertEquals(150, $tank->fuel_level);

        $this->assertFunctionThrowsException(function () use ($updated_operation) {
            $updated_operation->operation_date = Carbon::now();
            $updated_operation->save();
        });

        $tank->refresh();
        $this->assertEquals(150, $tank->fuel_level);
    }

    /** @test */
    public function it_allow_to_change_date_if_everything_is_ok(): void
    {
        $this->actingAs(User::first());
        $tank = FuelTank::factory()->create(['fuel_level' => 100]);
        FuelTankOperation::factory()->income()->create(['operation_date' => Carbon::now()->addDays(3), 'fuel_tank_id' => $tank->id, 'value' => 200]);
        $updated_operation = FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addDays(5), 'fuel_tank_id' => $tank->id, 'value' => 50]);

        $tank->refresh();
        $this->assertEquals(250, $tank->fuel_level);

        $this->assertFunctionDoesNotThrowException(function () use ($updated_operation) {
            $updated_operation->operation_date = Carbon::now();
            $updated_operation->save();
        });

        $tank->refresh();
        $this->assertEquals(250, $tank->fuel_level);
    }

    /** @test */
    public function it_recalc_future_operations_after_adding_to_the_past(): void
    {
        $tank = FuelTank::factory()->create(['fuel_level' => 0]);

        FuelTankOperation::factory()->income()->create(['fuel_tank_id' => $tank->id, 'value' => 2000]);
        FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addWeek(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        $future_operation = FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);

        $future_operation->refresh();
        $tank->refresh();

        $this->assertEquals($tank->fuel_level + 10, $future_operation->result_value);
    }

    /** @test */
    public function stored_result_value_must_calc_historical_fuel_level(): void
    {
        $tank = FuelTank::factory()->create(['fuel_level' => 0]);

        FuelTankOperation::factory()->income()->create(['fuel_tank_id' => $tank->id, 'value' => 2000]);
        $last_operation = FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addWeek(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        //give some history
        $tank->refresh();
        // 2000 - 10
        // 1990 - 10
        // 1980
        $this->assertEquals($tank->fuel_level, $last_operation->refresh()->result_value, 'it does not work well');

        //then add new operation

        $intermediate_factory = FuelTankOperation::factory()->income()->create(['operation_date' => Carbon::now()->addDays(3), 'fuel_tank_id' => $tank->id, 'value' => 20]);

        //that operation come before last operation, so it should take into account not current level, but one that was at time of the operation
        // 2000 - 10
        // 1990 + 10 //here id intermediate (it works not with 1980, but 1990)
        // 2000 - 10
        // 1990

        $this->assertEquals(2010, $intermediate_factory->result_value, 'result history is broken');

        $last_operation->refresh();
        $tank->refresh();

        $this->assertEquals($tank->fuel_level, $last_operation->refresh()->result_value, 'final level test crashed');
    }

    /** @test */
    public function it_recalc_manual_update_correctly(): void
    {
        $tank = FuelTank::factory()->create(['fuel_level' => 1000]);

        $operation_now = FuelTankOperation::factory()->income()->create(['fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->outgo()->create(['operation_date' => Carbon::now()->addMinute(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        $future_operation = FuelTankOperation::factory()->manual()->create(['operation_date' => Carbon::now()->addDay(), 'fuel_tank_id' => $tank->id, 'value' => 10]);
        FuelTankOperation::factory()->income()->create(['operation_date' => Carbon::now()->subMinute(), 'fuel_tank_id' => $tank->id, 'value' => 10]);

        $old_result = $operation_now->fuel_tank()->first()->fuel_level;

        $operation_now->value += 10;
        $operation_now->save();
        $future_operation->operation_date = Carbon::now()->subWeek();
        $future_operation->description = 'kuks';
        $future_operation->save();

        $future_operation->refresh();

        $this->assertEquals($old_result + 10, $future_operation->result_value);
    }

    /**
     * Asserts if any error occur during function execution
     */
    public function assertFunctionThrowsException($functionToTest): void
    {
        $error_caught = false;
        try {
            $functionToTest();
        } catch (\Throwable $e) {
            $error_caught = true;
        }

        $this->assertTrue($error_caught, 'Exception did not occur');
    }

    /**
     * Asserts if no errors occur during function execution
     */
    public function assertFunctionDoesNotThrowException($functionToTest): void
    {
        $error_caught = false;
        try {
            $functionToTest();
        } catch (\Throwable $e) {
            $error_caught = true;
        }

        $this->assertFalse($error_caught, 'There was an Exception');
    }
}

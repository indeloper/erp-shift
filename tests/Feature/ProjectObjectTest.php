<?php

namespace Tests\Feature;

use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use Tests\TestCase;

class ProjectObjectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_get_fuel_tank()
    {
        $object = ProjectObject::first();
        $tank = FuelTank::factory()->create(['object_id' => $object->id]);

        $this->assertEquals($tank->id, $object->fuel_tanks()->first()->id);
    }

    /** @test */
    public function it_returns_last_ten_operations()
    {
        $object = ProjectObject::first();
        $tank1 = FuelTank::factory()->create(['object_id' => $object->id]);
        FuelTankOperation::factory()->count(15)->create(['fuel_tank_id' => $tank1->id]);
        $tank = FuelTank::factory()->create(['object_id' => $object->id]);
        $operations = FuelTankOperation::factory()->count(15)->create(['fuel_tank_id' => $tank->id]);

        $this->assertEquals($operations->reverse()->pluck('id')->take(10), $object->getLastTenOperations()->pluck('id'));
    }

    /** @test */
    public function name_tag_getter_can_return_location_if_object_does_not_have_short_name()
    {
        // Given object without short name
        $object = ProjectObject::factory()->create(['short_name' => null]);

        // When we get name tag property of object
        $nameTag = $object->name_tag;

        // Then $nameTag should be equal to location
        $this->assertEquals($object->location, $nameTag);
    }

    /** @test */
    public function name_tag_getter_can_return_short_name_if_object_have_short_name()
    {
        // Given object without short name
        $object = ProjectObject::factory()->create(['short_name' => 'TIMELESS']);

        // When we get name tag property of object
        $nameTag = $object->name_tag;

        // Then $nameTag should be equal to location
        $this->assertEquals($object->short_name, $nameTag);
    }
}

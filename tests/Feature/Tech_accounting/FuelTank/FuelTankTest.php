<?php

namespace Tests\Feature\Tech_accounting\FuelTank;

use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FuelTankTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rps = Group::with('users')->find([27, 13, 19, 47])->pluck('users')->flatten();
        $this->response_user = $this->rps->random();
        $this->actingAs($this->response_user);
    }

    public function testStoreFuelTank(): void
    {
        $countFuelTanks = FuelTank::count();

        $response = $this->post(route('building::tech_acc::fuel_tank.store'), FuelTank::factory()->make()->toArray());

        $this->assertEquals($countFuelTanks + 1, FuelTank::count());
        $this->assertEquals(200, $response->status());
    }

    public function testUpdateFuelTank(): void
    {
        $fuelTank = FuelTank::factory()->create();

        $updatedFuelTank = FuelTank::factory()
            ->make([
                'object_id' => ProjectObject::factory()->create()->id,
                'explotation_start' => Carbon::parse($fuelTank->explotation_start)->addHour(),
            ]);

        $response = $this->put(route('building::tech_acc::fuel_tank.update', $fuelTank->id), $updatedFuelTank->toArray());

        $latestFuelTank = FuelTank::latest()->first();

        $this->assertEquals($updatedFuelTank->object_id, $latestFuelTank->object_id);
        $this->assertEquals($updatedFuelTank->explotation_start, $latestFuelTank->explotation_start);
        $this->assertEquals(200, $response->status());
    }

    public function testDestroyFuelTank(): void
    {
        $fuelTank = FuelTank::factory()->create();
        $countFuelTanks = FuelTank::count();

        $this->actingAs(User::where('group_id', 15)->active()->inRandomOrder()->first());

        $response = $this->delete(route('building::tech_acc::fuel_tank.update', $fuelTank->id));

        $this->assertEquals($countFuelTanks - 1, FuelTank::count());
        $this->assertEquals(200, $response->status());
    }

    public function testUpdateFuelTankLevel(): void
    {
        $fuelTank = FuelTank::factory()->create();

        $response = $this->post(route('building::tech_acc::fuel_tank.change_fuel_level', $fuelTank->id), [
            'fuel_level' => $fuelTank->fuel_level - 100,
            'description' => $this->faker()->sentence,
        ]);
        $fuelTankUpdated = FuelTank::with('operations')->find($fuelTank->id);
        $newOperation = $fuelTankUpdated->operations()->where('type', 3)->latest()->first();

        $this->assertEquals($fuelTank->fuel_level - 100, $fuelTankUpdated->fuel_level);
        $this->assertEquals($newOperation->value, -100);
        $this->assertEquals($newOperation->result_value, $fuelTankUpdated->fuel_level);
        $this->assertEquals($newOperation->result_value, $fuelTank->fuel_level - 100);
        $this->assertEquals(200, $response->status());
    }

    public function testSomeOneWhoCantStoreFuelTank(): void
    {
        $countFuelTanks = FuelTank::count();
        $user = User::whereNotIn('group_id', [27, 13, 19, 47])->where('id', '!=', 1)->active()->inRandomOrder()->first();
        $this->actingAs($user);

        $response = $this->post(route('building::tech_acc::fuel_tank.store'), FuelTank::factory()->make()->toArray());

        $this->assertEquals(403, $response->status());
    }

    public function testSomeOneWhoCantUpdateFuelTank(): void
    {
        $user = User::active()->whereNotIn('group_id', [27, 13, 19, 47])
            ->where('id', '!=', 1)
            ->inRandomOrder()
            ->first();

        $this->actingAs($user);

        $fuelTank = FuelTank::factory()->create();
        $updatedFuelTank = FuelTank::factory()->make();

        $response = $this->put(route('building::tech_acc::fuel_tank.update', $fuelTank->id), $updatedFuelTank->toArray());

        $this->assertEquals(403, $response->status());
    }

    public function testSomeOneWhoCantDestroyFuelTank(): void
    {
        $fuelTank = FuelTank::factory()->create();

        $user = User::whereNotIn('group_id', [15])
            ->active()
            ->whereNotIn('id', [User::getModel()->main_logist_id, 1])
            ->inRandomOrder()
            ->first();

        $this->actingAs($user);

        $response = $this->delete(route('building::tech_acc::fuel_tank.update', $fuelTank->id));

        $this->assertEquals(403, $response->status());
    }

    public function testIndexFuelTank(): void
    {
        $response = $this->get(route('building::tech_acc::fuel_tank.index'));

        $this->assertEquals(200, $response->status());
        $response->assertSee('Топливные ёмкости');
    }

    /** @test */
    public function it_returns_tanks_on_getter(): void
    {
        $this->actingAs(User::active()->first());
        $countFuelTanks = FuelTank::count();
        FuelTank::factory()->count(3)->create();

        $response = $this->post(route('building::tech_acc::get_fuel_tanks'))->assertStatus(200);

        if (($countFuelTanks + 3) >= 10) {
            $countFuelTankAssert = 10;
        } else {
            $countFuelTankAssert = 3 + $countFuelTanks;
        }

        $this->assertCount($countFuelTankAssert, $response->json());
    }

    public function testFilterFuelTankFuelLevel(): void
    {
        FuelTank::factory()->count(10)->create();

        $data = ['url' => '/smth?fuel_level_from=10&page=1&fuel_level_to=50000&tank_number=12'];
        $response = $this->post(route('building::tech_acc::get_fuel_tanks_paginated', $data))->assertStatus(200);

        $countFuelTankAssert = FuelTank::where([
            ['fuel_level', '>=', 10],
            ['fuel_level', '<=', 50000],
            ['tank_number', 'like', '%12%'],
        ])->count();

        $this->assertEquals($countFuelTankAssert, $response->json()['fuelTanksCount']);
    }
}

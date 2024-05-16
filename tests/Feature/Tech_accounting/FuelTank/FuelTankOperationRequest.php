<?php

namespace Tests\Feature\Tech_accounting\FuelTank;

use App\Models\FileEntry;
use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use Tests\TestCase;

class FuelTankOperationRequest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(Group::find(Group::PROJECT_MANAGERS)->first()->getUsers()->first());
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function it_stores_valid_request()
    {
        $test_request = FuelTankOperation::factory()->raw();
        $test_request['file_ids'] = FileEntry::factory()->count(2)->create(['mime' => 'video'])->pluck('id');

        $this->post(route('building::tech_acc::fuel_tank_operations.store'), $test_request)->assertSessionDoesntHaveErrors()->assertStatus(200);

        $this->assertEquals($test_request['value'], FuelTankOperation::first()->value);
    }

    /** @test */
    public function it_destroys_operation_by_id()
    {
        $operation = FuelTankOperation::factory()->create();

        $this->delete(route('building::tech_acc::fuel_tank_operations.destroy', $operation->id))->assertStatus(200);

        $this->assertEmpty(FuelTankOperation::all());
    }

    /** @test */
    public function it_updates_operation_with_valid_request()
    {
        $operation = FuelTankOperation::factory()->create();
        $test_request = FuelTankOperation::factory()->raw();

        $this->put(route('building::tech_acc::fuel_tank_operations.update', $operation->id), $test_request)->assertStatus(200);

        $operation->refresh();
        $this->assertEquals($operation->value, $test_request['value']);
    }

    /** @test */
    public function it_updates_operation_with_files()
    {
        $operation = FuelTankOperation::factory()->create();

        $file_to_stay = FileEntry::factory()->create();
        $operation->attachFiles(FileEntry::factory()->count(5)->create()->pluck('id')->merge($file_to_stay->id));

        $test_request = FuelTankOperation::factory()->raw();
        $test_request['file_ids'] = FileEntry::factory()->count(5)->create()->pluck('id')->merge($file_to_stay->id);

        $this->put(route('building::tech_acc::fuel_tank_operations.update', $operation->id), $test_request)->assertStatus(200);

        $operation->refresh();
        $this->assertEquals(6, $operation->files()->count());
    }

    /** @test */
    public function it_also_attaches_files_with_valid_request()
    {
        $test_request = FuelTankOperation::factory()->raw();
        $test_request['file_ids'] = FileEntry::factory()->count(3)->create()->pluck('id');
        $test_request['file_ids'] = $test_request['file_ids']->merge(FileEntry::factory()->count(2)->create(['mime' => 'video'])->pluck('id'));

        $this->post(route('building::tech_acc::fuel_tank_operations.store'), $test_request)->assertStatus(200);

        $this->assertCount(5, FuelTankOperation::first()->files);
    }

    /** @test */
    public function it_validates_request_by_type()
    {
        $this->withExceptionHandling();

        $income_request = [
            'fuel_tank_id' => $this->faker->randomNumber(3),
            'author_id' => 1,
            'object_id' => ProjectObject::inRandomOrder()->first()->id,
            'our_technic_id' => OurTechnic::factory()->create(),
            'value' => $this->faker->randomFloat(3, 0, 300),
            'type' => 1,
            'description' => $this->faker->text(),
            'operation_date' => $this->faker->dateTime(),
        ];

        $response = $this->post(route('building::tech_acc::fuel_tank_operations.store'), $income_request)->assertStatus(302);

        $response->assertSessionHasErrors('contractor_id');
        $this->assertEmpty(FuelTankOperation::all());
    }

    /** @test */
    public function user_from_another_group_cant_store_operation()
    {
        $this->withExceptionHandling();
        $this->actingAs(User::active()->take(2)->get()->last());
        $test_request = FuelTankOperation::factory()->raw();

        $this->post(route('building::tech_acc::fuel_tank_operations.store'), $test_request)->assertSessionDoesntHaveErrors()->assertStatus(403);

        $this->assertEmpty(FuelTankOperation::all());
    }

    /** @test */
    public function it_deletes_operation_on_method()
    {
        $operation = FuelTankOperation::factory()->create();
        $this->delete(route('building::tech_acc::fuel_tank_operations.destroy', $operation->id))->assertStatus(200);

        $this->assertSoftDeleted($operation);
    }

    /** @test */
    public function it_forbid_to_delete_for_users_without_permission()
    {
        $this->actingAs(User::whereIn('group_id', Group::FOREMEN)->inRandomOrder()->first());

        $this->withExceptionHandling();

        $operation = FuelTankOperation::factory()->create();
        $this->delete(route('building::tech_acc::fuel_tank_operations.destroy', $operation->id))->assertStatus(403);
    }

    /** @test */
    public function it_returns_full_data_on_show_method()
    {
        $operation = FuelTankOperation::factory()->income()->create();

        $response = $this->get(route('building::tech_acc::fuel_tank_operations.show', $operation->id))
            ->assertStatus(200);

        $this->assertEquals($operation->contractor->full_name, $response->json('data.operation.contractor.full_name'));
    }

    /** @test */
    public function it_paginate_results_for_index()
    {
        $all_operations = FuelTankOperation::factory()->count(11)->create();

        $response = $this->get(route('building::tech_acc::fuel_tank_operations.index', ['page' => 2]))->assertStatus(200);

        $view_operations = $response->viewData('data')['operations'];

        // look for FuelTankOperationController@index.
        // enable pagination there first
        // then toggle comments below

        $this->assertNotEquals($all_operations->first()->id, $view_operations[0]->id);
        //        $this->assertEquals($all_operations->first()->id, $view_operations[0]->id);
    }
}

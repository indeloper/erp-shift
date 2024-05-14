<?php

namespace Tests\Feature;

use App\Models\Contractors\Contractor;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ContractorTypeTest extends TestCase
{
    use WithoutMiddleware;

    protected $migration;

    protected $customer;

    protected $contractor;

    protected $supplier;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = Contractor::CUSTOMER;
        $this->contractor = Contractor::CONTRACTOR;
        $this->supplier = Contractor::SUPPLIER;

        Contractor::query()->delete();
        $this->withExceptionHandling();
    }

    /** @test */
    public function a_standard_contractor_doesnt_have_type(): void
    {
        // Given fresh contractor
        $contractor = factory(Contractor::class)->create();

        // Then $contractor main_type property should be equal to null
        $this->assertNull($contractor->main_type);
        // And types getter should return 'Не указан'
        $this->assertEquals('Не указан', $contractor->types);
    }

    /** @test */
    public function types_getter_test(): void
    {
        // Given contractors with all existed types
        $customer = factory(Contractor::class)->create(['main_type' => $this->customer]);
        $contractor = factory(Contractor::class)->create(['main_type' => $this->contractor]);
        $supplier = factory(Contractor::class)->create(['main_type' => $this->supplier]);

        // Then types getter should return this values
        $this->assertEquals('Заказчик', $customer->types);
        $this->assertEquals('Подрядчик', $contractor->types);
        $this->assertEquals('Поставщик', $supplier->types);
    }

    /** @test */
    public function scope_by_type_test(): void
    {
        // Given contractors with all existed types
        $standard = factory(Contractor::class)->create();
        $customer = factory(Contractor::class)->create(['main_type' => $this->customer]);
        $contractor = factory(Contractor::class)->create(['main_type' => $this->contractor]);
        $supplier = factory(Contractor::class)->create(['main_type' => $this->supplier]);

        // When we make scope queries
        $queryForCustomers = Contractor::byType($this->customer)->get();
        $queryForContractors = Contractor::byType($this->contractor)->get();
        $queryForSuppliers = Contractor::byType($this->supplier)->get();

        // Then contractors with given type should be on top of collection
        $this->assertEquals($this->customer, $queryForCustomers->first()->main_type);
        $this->assertEquals($this->contractor, $queryForContractors->first()->main_type);
        $this->assertEquals($this->supplier, $queryForSuppliers->first()->main_type);
        // And in collection should be contractor without type after contractors with type
        $this->assertEquals([$this->customer, null], $queryForCustomers->pluck('main_type')->toArray());
        $this->assertEquals([$this->contractor, null], $queryForContractors->pluck('main_type')->toArray());
        $this->assertEquals([$this->supplier, null], $queryForSuppliers->pluck('main_type')->toArray());
        // And in collection contractor_id should be in right order
        $this->assertEquals([$customer->id, $standard->id], $queryForCustomers->pluck('id')->toArray());
        $this->assertEquals([$contractor->id, $standard->id], $queryForContractors->pluck('id')->toArray());
        $this->assertEquals([$supplier->id, $standard->id], $queryForSuppliers->pluck('id')->toArray());
    }

    /** @test */
    public function main_type_is_required_for_contractor_create(): void
    {
        // Given user
        $user = User::first();

        // When we make post request with data without main_type
        $response = $this->actingAs($user)->post(route('contractors::store'), [
            'full_name' => 'bla-bla',
            'short_name' => 'bla',
            'phone_count' => [1],
        ]);

        // Then session should have errors
        $response->assertSessionHasErrors('types');
    }

    /** @test */
    public function main_type_is_required_for_contractor_update(): void
    {
        // Given user and contractor
        $user = User::first();
        $contractor = factory(Contractor::class)->create();

        // When we make post request with data without main_type
        $response = $this->actingAs($user)->post(route('contractors::update', $contractor->id), [
            'full_name' => 'bla-bla',
            'short_name' => 'bla',
            'phone_count' => [1],
        ]);

        // Then session should have errors
        $response->assertSessionHasErrors('types');
    }

    /** @test */
    public function contractor_can_have_additional_types(): void
    {
        // Given contractor
        $contractor = factory(Contractor::class)->create(['main_type' => $this->supplier]);

        // When we add other types to contractor
        $contractor->additional_types()->createMany([
            [
                'additional_type' => $this->contractor,
                'user_id' => 1,
            ],
            [
                'additional_type' => $this->customer,
                'user_id' => 1,
            ],
        ]);

        // Then contractor should have additional types
        $this->assertEquals($contractor->additional_types->pluck('additional_type'), collect([$this->contractor, $this->customer]));
        // And types getter should return all types separated by comma
        $this->assertEquals('Поставщик, Подрядчик, Заказчик', $contractor->types);
    }

    /** @test */
    public function we_can_create_contractor_with_additional_types(): void
    {
        // Given user
        $user = User::first();

        // When we make post request with data with types array
        $response = $this->actingAs($user)->post(route('contractors::store'), [
            'full_name' => 'bla-bla',
            'short_name' => 'bla',
            'phone_count' => [1],
            'types' => [$this->supplier, $this->contractor],
        ]);

        // Then we must have new contractor in DB
        $contractor = Contractor::first();
        $this->assertEquals(['bla-bla', 'bla', $this->supplier], [$contractor->full_name, $contractor->short_name, $contractor->main_type]);
        $this->assertCount(1, $contractor->additional_types);
        $this->assertEquals($this->contractor, $contractor->additional_types[0]->additional_type);
        $this->assertEquals('Поставщик, Подрядчик', $contractor->types);
    }

    /** @test */
    public function we_can_remove_contractor_additional_types_by_updating(): void
    {
        /// Given user and contractor
        $user = User::first();
        $contractor = factory(Contractor::class)->create(['main_type' => $this->supplier]);
        $contractor->additional_types()->createMany([
            [
                'additional_type' => $this->contractor,
                'user_id' => 1,
            ],
            [
                'additional_type' => $this->customer,
                'user_id' => 1,
            ],
        ]);

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('contractors::update', $contractor->id), [
            'full_name' => 'bla-bla',
            'short_name' => 'bla',
            'phone_count' => [1],
            'types' => [$contractor->main_type],
        ]);

        // Then we must have fresh contractor in DB
        $contractor->refresh();
        $this->assertEquals(['bla-bla', 'bla', $this->supplier], [$contractor->full_name, $contractor->short_name, $contractor->main_type]);
        $this->assertCount(0, $contractor->additional_types);
        $this->assertEquals('Поставщик', $contractor->types);
    }

    /** @test */
    public function we_can_add_contractor_additional_types_by_updating(): void
    {
        /// Given user and contractor
        $user = User::first();
        $contractor = factory(Contractor::class)->create(['main_type' => $this->supplier]);

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('contractors::update', $contractor->id), [
            'full_name' => 'bla-bla',
            'short_name' => 'bla',
            'phone_count' => [1],
            'types' => [$contractor->main_type, $this->contractor, $this->customer],
        ]);

        // Then we must have fresh contractor in DB
        $contractor->refresh();
        $this->assertEquals(['bla-bla', 'bla', $this->supplier], [$contractor->full_name, $contractor->short_name, $contractor->main_type]);
        $this->assertCount(2, $contractor->additional_types);
        $this->assertEquals('Поставщик, Подрядчик, Заказчик', $contractor->types);
    }

    /** @test */
    public function we_can_change_contractor_additional_types_by_updating(): void
    {
        /// Given user and contractor
        $user = User::first();
        $contractor = factory(Contractor::class)->create(['main_type' => $this->supplier]);
        $contractor->additional_types()->create([
            'additional_type' => $this->contractor,
            'user_id' => 1,
        ]);

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('contractors::update', $contractor->id), [
            'full_name' => 'bla-bla',
            'short_name' => 'bla',
            'phone_count' => [1],
            'types' => [$contractor->main_type, $this->customer],
        ]);

        // Then we must have fresh contractor in DB
        $contractor->refresh();
        $this->assertEquals(['bla-bla', 'bla', $this->supplier], [$contractor->full_name, $contractor->short_name, $contractor->main_type]);
        $this->assertCount(1, $contractor->additional_types);
        $this->assertEquals('Поставщик, Заказчик', $contractor->types);
    }

    /** @test */
    public function scope_by_type_work_with_additional_types(): void
    {
        // Given contractor with additional types
        $contractor = factory(Contractor::class)->create(['main_type' => $this->supplier]);
        $contractor->additional_types()->createMany([
            [
                'additional_type' => $this->contractor,
                'user_id' => 1,
            ],
            [
                'additional_type' => $this->customer,
                'user_id' => 1,
            ],
        ]);
        $standard = factory(Contractor::class)->create();

        // When we make scope queries
        $queryForSuppliers = Contractor::byType($this->supplier)->get();

        // Then contractor should be on top of collection
        $this->assertEquals($this->supplier, $queryForSuppliers->first()->main_type);
        $this->assertEquals($contractor->id, $queryForSuppliers->first()->id);
        // And in collection should be contractor without type after contractors with type
        $this->assertEquals([$this->supplier, null], $queryForSuppliers->pluck('main_type')->toArray());
        // And in collection contractor_id should be in right order
        $this->assertEquals([$contractor->id, $standard->id], $queryForSuppliers->pluck('id')->toArray());
    }
}

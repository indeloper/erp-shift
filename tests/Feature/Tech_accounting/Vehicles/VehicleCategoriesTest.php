<?php

namespace Tests\Feature\Tech_accounting\Vehicles;

use App\Models\TechAcc\Vehicles\OurVehicleParameters;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleCategoriesTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    const BLA_BLA = 'bla-bla';

    const BLA = 'bla';

    const CHAR_NAME = 'b';

    const CHAR_UNIT = 'мм';

    const GROUPS_WITH_PERMISSIONS = [15, 17, 47];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::whereIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();
    }

    /** @test */
    public function we_can_create_vehicle_category(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();

        // Then this category should be exemplar of VehicleCategories class
        $this->assertTrue(get_class($vehicleCategory) == VehicleCategories::class);
    }

    /** @test */
    public function we_can_create_vehicle_category_characteristic(): void
    {
        // Given fresh vehicle category characteristic
        $vehicleCategoryCharacteristic = VehicleCategoryCharacteristics::factory()->create();

        // Then this characteristic should be exemplar of VehicleCategoryCharacteristics class
        $this->assertTrue(get_class($vehicleCategoryCharacteristic) == VehicleCategoryCharacteristics::class);
    }

    /** @test */
    public function standard_vehicle_category_characteristic_must_show(): void
    {
        // Given fresh vehicle category characteristic
        // ('show' => 1 imitate database save (default value of this field is 1))
        $vehicleCategoryCharacteristic = VehicleCategoryCharacteristics::factory()->create(['show' => 1]);

        // Then this category should be exemplar of VehicleCategories class
        $this->assertEquals(1, $vehicleCategoryCharacteristic->show);
    }

    /** @test */
    public function standard_vehicle_category_characteristic_must_have_vehicle_category_relation(): void
    {
        // Given fresh vehicle category characteristic
        // ('show' => 1 imitate database save (default value of this field is 1))
        $vehicleCategoryCharacteristic = VehicleCategoryCharacteristics::factory()->create(['show' => 1]);

        // Then this characteristic relation should be exemplar of VehicleCategories class
        $this->assertTrue(get_class($vehicleCategoryCharacteristic->category) == VehicleCategories::class);
    }

    /** @test */
    public function vehicle_category_have_author_relation(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();

        // Then relation should return exemplar of User class
        $this->assertTrue(get_class($vehicleCategory->author) == User::class);
    }

    /** @test */
    public function vehicle_category_can_have_characteristics_relation(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();

        // Then relation should return collection
        $this->assertTrue($vehicleCategory->characteristics instanceof Collection);
    }

    /** @test */
    public function vehicle_category_can_have_characteristics_relation_second_test(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);

        // Then relation should return collection with count 3
        $this->assertCount(3, $vehicleCategory->characteristics);
    }

    /** @test */
    public function if_we_remove_vehicle_category_characteristic_then_vehicle_category_relation_collection_count_should_decrease(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        $willDelete = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);

        // Then relation should return collection with count 3
        $this->assertCount(3, $vehicleCategory->characteristics);

        // When we delete one of characteristics
        $willDelete->delete();
        $vehicleCategory->refresh();

        // Then relations should return collection with count 2
        $this->assertCount(2, $vehicleCategory->characteristics);
    }

    /** @test */
    public function we_cant_create_vehicle_category_by_post_without_permissions(): void
    {
        // Given user
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.store'), [
            'name' => self::BLA_BLA,
            'description' => self::BLA,
            'characteristics' => [
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT,
                ],
            ],
        ]);

        // Then we must have forbidden error
        $response->assertForbidden();
    }

    /** @test */
    public function we_can_create_vehicle_category_by_post(): void
    {
        // Given user
        $user = $this->user;

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.store'), [
            'name' => self::BLA_BLA,
            'description' => self::BLA,
            'characteristics' => [
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'required' => 0,
                    'unit' => self::CHAR_UNIT,
                ],
            ],
        ]);

        // Then in session shouldn't be errors
        $response->assertSessionHasNoErrors();
        /** Little notice here
         * Because we use transactions in controller method
         * We cant see fresh category in database,
         * but can see in Laravel collection
         */
        $createdRow = VehicleCategories::get()->last();
        // And in DB must be new VehicleCategory with our info
        $this->assertEquals([$user->id, self::BLA_BLA, self::BLA],
            [$createdRow->user_id, $createdRow->name, $createdRow->description]);
        // Also, category must have relation
        $this->assertCount(1, $createdRow->characteristics);
        $relation = $createdRow->characteristics->first();
        $this->assertEquals([self::CHAR_NAME, 1, 0, self::CHAR_UNIT],
            [$relation->name, $relation->show, $relation->required, $relation->unit]);
    }

    /** @test */
    public function we_can_create_vehicle_category_by_post_without_description(): void
    {
        // Given user
        $user = $this->user;

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.store'), [
            'name' => self::BLA_BLA,
            'description' => '',
            'characteristics' => [
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'required' => 0,
                    'unit' => self::CHAR_UNIT,
                ],
            ],
        ]);

        // Then in session shouldn't be errors
        $response->assertSessionHasNoErrors();
        /** Little notice here
         * Because we use transactions in controller method
         * We cant see fresh category in database,
         * but can see in Laravel collection
         */
        $createdRow = VehicleCategories::get()->last();
        // And in DB must be new VehicleCategory with our info
        $this->assertEquals([$user->id, self::BLA_BLA, ''],
            [$createdRow->user_id, $createdRow->name, $createdRow->description]);
        // Also, category must have relation
        $this->assertCount(1, $createdRow->characteristics);
        $relation = $createdRow->characteristics->first();
        $this->assertEquals([self::CHAR_NAME, 1, 0, self::CHAR_UNIT],
            [$relation->name, $relation->show, $relation->required, $relation->unit]);
    }

    /** @test */
    public function we_cant_create_vehicle_category_by_post_without_name(): void
    {
        // Given user
        $user = $this->user;
        // Turn handling on
        $this->withExceptionHandling();

        // When we make post request with data
        $name = self::BLA_BLA;
        $description = self::BLA;
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.store'), [
            //'name' => $name,
            'description' => $description,
            'characteristics' => [
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT,
                ],
            ],
        ]);

        // Then in session should be errors
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function we_can_create_vehicle_category_by_post_without_characteristics(): void
    {
        // Given user
        $user = $this->user;

        // When we make post request with data
        $name = self::BLA_BLA;
        $description = self::BLA;
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.store'), [
            'name' => $name,
            'description' => $description,
        ]);

        // Then in session should be errors
        $response->assertSessionDoesntHaveErrors('characteristics');
    }

    /** @test */
    public function we_cant_create_vehicle_category_by_post_without_characteristic_name_and_show_option(): void
    {
        // Given user
        $user = $this->user;
        // Turn handling on
        $this->withExceptionHandling();

        // When we make post request with data
        $name = self::BLA_BLA;
        $description = self::BLA;
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.store'), [
            //'name' => $name,
            'description' => $description,
            'characteristics' => [
                [
                    //'name' => self::CHAR_NAME,
                    //'show' => 1,
                    'unit' => self::CHAR_UNIT,
                ],
            ],
        ]);

        // Then in session should be errors
        $response->assertSessionHasErrors('characteristics.*.name');
        $response->assertSessionHasErrors('characteristics.*.show');
    }

    /** @test */
    public function we_cant_create_vehicle_category_by_post_with_characteristics_lenght_more_than_ten(): void
    {
        // Given user
        $user = $this->user;
        // Turn handling on
        $this->withExceptionHandling();

        // When we make post request with data
        $name = self::BLA_BLA;
        $description = self::BLA;
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.store'), [
            'name' => $name,
            'description' => $description,
            'characteristics' => [
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT,
                ],
                [
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'unit' => self::CHAR_UNIT. 1,
                ],
                [
                    'name' => self::CHAR_NAME. 2,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT. 2,
                ],
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT,
                ],
                [
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'unit' => self::CHAR_UNIT. 1,
                ],
                [
                    'name' => self::CHAR_NAME. 2,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT. 2,
                ],
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT,
                ],
                [
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'unit' => self::CHAR_UNIT. 1,
                ],
                [
                    'name' => self::CHAR_NAME. 2,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT. 2,
                ],
                [
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'unit' => self::CHAR_UNIT. 1,
                ],
                [
                    'name' => self::CHAR_NAME. 2,
                    'show' => 1,
                    'unit' => self::CHAR_UNIT. 2,
                ],
            ],
        ]);

        // Then in session should be errors
        $response->assertSessionHasErrors('characteristics');
    }

    /** @test */
    public function we_can_delete_the_vehicle_category(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();

        $vehicleCategory->delete();
        $vehicleCategory->fresh();

        // Then category must be deleted
        $this->assertTrue($vehicleCategory->trashed());
    }

    /** @test */
    public function anyone_without_permission_cant_delete_the_vehicle_category(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();

        /// Given user
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // When we make post request with data
        $response = $this->actingAs($user)->delete(route('building::vehicles::vehicle_categories.destroy', $vehicleCategory->id));

        // Then we must have forbidden error
        $response->assertForbidden();
    }

    /** @test */
    public function anyone_with_permission_can_delete_the_vehicle_category(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        /// Given user
        $user = $this->user;

        // When we make post request with data
        $response = $this->actingAs($user)->delete(route('building::vehicles::vehicle_categories.destroy', $vehicleCategory->id));
        // And refresh our category
        $vehicleCategory->refresh();

        // Then category should be soft deleted
        $this->assertTrue($vehicleCategory->trashed());
    }

    /** @test */
    public function anyone_without_permission_cant_go_to_edit_page(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();

        /// Given user
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // When we make post request with data
        $response = $this->actingAs($user)->get(route('building::vehicles::vehicle_categories.edit', $vehicleCategory->id));

        // Then we must have forbidden error
        $response->assertForbidden();
    }

    /** @test */
    public function anyone_with_permission_can_go_to_edit_page(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();

        /// Given user
        $user = $this->user;

        // When we make post request with data
        $response = $this->actingAs($user)->get(route('building::vehicles::vehicle_categories.edit', $vehicleCategory->id));

        // Then everything should be OK
        $response->assertOk();
    }

    /** @test */
    public function anyone_without_permission_cant_go_to_create_page(): void
    {
        // Given user
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // When we make post request with data
        $response = $this->actingAs($user)->get(route('building::vehicles::vehicle_categories.create'));

        // Then we must have forbidden error
        $response->assertForbidden();
    }

    /** @test */
    public function anyone_with_permission_can_go_to_create_page(): void
    {
        /// Given user
        $user = $this->user;

        // When we make post request with data
        $response = $this->actingAs($user)->get(route('building::vehicles::vehicle_categories.create'));

        // Then everything should be OK
        $response->assertOk();
    }

    /** @test */
    public function we_can_add_more_characteristics_for_vehicle_category_by_post(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given user
        $user = $this->user;

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.update', $vehicleCategory->id), [
            'name' => self::BLA_BLA,
            'description' => self::BLA,
            'characteristics' => [
                [
                    'name' => self::CHAR_NAME,
                    'show' => 1,
                    'required' => 1,
                    'unit' => self::CHAR_UNIT,
                ],
                [
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'required' => 0,
                    'unit' => self::CHAR_UNIT. 1,
                ],
            ],
        ]);
        // And refresh our category
        $vehicleCategory->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And our category must have five characteristics
        $this->assertCount(5, $vehicleCategory->characteristics);
    }

    /** @test */
    public function we_can_update_characteristic_in_vehicle_category_by_post(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        $willUpdate = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given user
        $user = $this->user;

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.update', $vehicleCategory->id), [
            'name' => self::BLA_BLA,
            'description' => self::BLA,
            'characteristics' => [
                [
                    'id' => $willUpdate->id,
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'required' => 1,
                    'unit' => self::CHAR_UNIT. 1,
                ],
            ],
        ]);
        // And refresh our category
        $vehicleCategory->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And our category characteristic must change
        $characteristic = $vehicleCategory->characteristics->find($willUpdate->id);
        $this->assertEquals(self::CHAR_NAME. 1, $characteristic->name);
        $this->assertEquals(0, $characteristic->show);
        $this->assertEquals(self::CHAR_UNIT. 1, $characteristic->unit);
        $this->assertEquals(1, $characteristic->required);
    }

    /** @test */
    public function we_can_update_characteristic_in_vehicle_category_by_post_without_description(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        $willUpdate = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given user
        $user = $this->user;

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.update', $vehicleCategory->id), [
            'name' => self::BLA_BLA,
            'description' => '',
            'characteristics' => [
                [
                    'id' => $willUpdate->id,
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'required' => 1,
                    'unit' => self::CHAR_UNIT. 1,
                ],
            ],
        ]);
        // And refresh our category
        $vehicleCategory->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And our category characteristic must change
        $characteristic = $vehicleCategory->characteristics->find($willUpdate->id);
        $this->assertEquals(self::CHAR_NAME. 1, $characteristic->name);
        $this->assertEquals(0, $characteristic->show);
        $this->assertEquals(1, $characteristic->required);
        $this->assertEquals(self::CHAR_UNIT. 1, $characteristic->unit);
        $this->assertEquals('', $vehicleCategory->description);
    }

    /** @test */
    public function we_can_delete_characteristic_in_vehicle_category_by_post(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        $willDelete = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given user
        $user = $this->user;

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.update', $vehicleCategory->id), [
            'name' => self::BLA_BLA,
            'description' => self::BLA,
            'deleted_characteristic_ids' => [$willDelete->id],
            'characteristics' => [
                [
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'required' => 1,
                    'unit' => self::CHAR_UNIT. 1,
                ],
            ],
        ]);
        // And refresh our category
        $vehicleCategory->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And our category characteristics count should be equal to 3 (was 3, then +1 by request and -1)
        $this->assertCount(3, $vehicleCategory->characteristics);
    }

    /** @test */
    public function we_cant_update_vehicle_category_by_post_without_name_but_can_without_characteristics(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given user
        $user = $this->user;

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.update', $vehicleCategory->id), [
            //            'name' => self::BLA_BLA,
            'description' => self::BLA,
            //            'characteristics' => [
            //                [
            //                    'name' => self::CHAR_NAME . 1,
            //                    'show' => 0,
            //                    'unit' => self::CHAR_UNIT . 1,
            //                ],
            //            ]
        ]);

        // Then in session should be errors
        $response->assertSessionHasErrors('name');
        $response->assertSessionDoesntHaveErrors('characteristics');
    }

    /** @test */
    public function any_authorized_user_can_go_to_index_page(): void
    {
        // Given user
        $user = User::inRandomOrder()->first();

        // When we make post request with data
        $response = $this->actingAs($user)->get(route('building::vehicles::vehicle_categories.index'));

        // Then we must have forbidden error
        $response->assertOk();
    }

    /** @test */
    public function vehicle_category_must_have_vehicles_relation(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create(['category_id' => $vehicleCategory->id]);

        // When we refresh our category
        $vehicleCategory->refresh();

        // Then our category vehicle relation should have count equal to 1
        $this->assertCount(1, $vehicleCategory->vehicles);
    }

    /** @test */
    public function vehicle_category_characteristic_have_parameters_relation(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        $characteristic = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given fresh vehicle with parameter
        $vehicle = OurVehicles::factory()->create(['category_id' => $vehicleCategory->id]);
        $parameter = OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id, 'characteristic_id' => $characteristic->id]);

        // When we refresh our characteristic
        $characteristic->refresh();

        // Then parameters relation should have count equal to 1
        $this->assertCount(1, $characteristic->parameters);
    }

    /** @test */
    public function category_characteristic_delete_influence(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        $willDelete = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given fresh vehicle with parameter
        $vehicle = OurVehicles::factory()->create(['category_id' => $vehicleCategory->id]);
        $parameter = OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id, 'characteristic_id' => $willDelete->id]);
        // Given user
        $user = $this->user;

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.update', $vehicleCategory->id), [
            'name' => self::BLA_BLA,
            'description' => self::BLA,
            'deleted_characteristic_ids' => [$willDelete->id],
        ]);
        // And refresh our category
        $vehicleCategory->refresh();
        // And refresh deleted characteristic
        $willDelete->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And our category characteristics count should be equal to 2
        $this->assertCount(2, $vehicleCategory->characteristics);
        // And parameters relation should have count equal to 0
        $this->assertCount(0, $willDelete->parameters);
        // Also vehicle parameters relation should be equal to 0
        $this->assertCount(0, $vehicle->parameters);
    }

    /** @test */
    public function category_characteristic_update_influence(): void
    {
        // Given fresh vehicle category
        $vehicleCategory = VehicleCategories::factory()->create();
        // Given three characteristics
        $willUpdate = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given fresh vehicle with parameter
        $vehicle = OurVehicles::factory()->create(['category_id' => $vehicleCategory->id]);
        OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id, 'characteristic_id' => $willUpdate->id]);
        // Given user
        $user = $this->user;

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.update', $vehicleCategory->id), [
            'name' => self::BLA_BLA,
            'description' => self::BLA,
            'characteristics' => [
                [
                    'id' => $willUpdate->id,
                    'name' => self::CHAR_NAME. 1,
                    'show' => 0,
                    'required' => 1,
                    'unit' => self::CHAR_UNIT. 1,
                ],
            ],
        ]);
        // And refresh our category
        $vehicleCategory->refresh();
        // And refresh updated characteristic
        $willUpdate->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And parameters relation should have same count
        $this->assertCount(1, $willUpdate->parameters);
        // Also vehicle parameters should lost value
        $this->assertEquals('', $vehicle->parameters->first()->value);
    }

    /** @test */
    public function category_deleting_influence_on_everything(): void
    {
        // Given fresh vehicle category with characteristic
        $vehicleCategory = VehicleCategories::factory()->create();
        $characteristic = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given fresh vehicle with parameter
        $vehicle = OurVehicles::factory()->create(['category_id' => $vehicleCategory->id]);
        $parameter = OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id, 'characteristic_id' => $characteristic->id]);

        // When we delete category
        $vehicleCategory->delete();

        // Refresh models
        $vehicleCategory->refresh();
        $characteristic->refresh();
        $vehicle->refresh();
        $parameter->refresh();

        // Then category should be deleted
        $this->assertTrue($vehicleCategory->trashed());
        // Category characteristics should be deleted - UPD NOW NOT
        //        $this->assertTrue($characteristic->trashed());
        // Category vehicles should be deleted
        $this->assertTrue($vehicle->trashed());
        // Vehicle parameters should be deleted - UPD NOW NOT
        //        $this->assertTrue($parameter->trashed());
    }

    /** @test */
    public function category_characteristic_deleting_influence(): void
    {
        // Given fresh vehicle category with characteristic
        $vehicleCategory = VehicleCategories::factory()->create();
        $characteristic = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given fresh vehicle with parameter
        $vehicle = OurVehicles::factory()->create(['category_id' => $vehicleCategory->id]);
        $parameter = OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id, 'characteristic_id' => $characteristic->id]);

        // When we delete category
        $characteristic->delete();

        // Refresh models
        $vehicleCategory->refresh();
        $characteristic->refresh();
        $vehicle->refresh();
        $parameter->refresh();

        // Then category characteristics should be deleted
        $this->assertTrue($characteristic->trashed());
        // Vehicle parameters should be deleted
        $this->assertTrue($parameter->trashed());
    }
}

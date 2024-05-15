<?php

namespace Tests\Feature\Tech_accounting\Vehicles;

use App\Models\FileEntry;
use App\Models\TechAcc\Vehicles\OurVehicleParameters;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OurVehiclesTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;

    protected $vehicle_category;

    const NUMBER = '012345678';

    const TRAILER_NUMBER = '876543210';

    const MARK = 'LADA';

    const MODEL = 'NINE';

    const OWNER = 1;

    const GROUPS_WITH_PERMISSIONS = [15, 17, 47];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::whereIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();
        // create category
        $this->vehicle_category = VehicleCategories::first() ?? VehicleCategories::factory()->create();
        // add characteristic
        VehicleCategoryCharacteristics::factory()->create(['category_id' => $this->vehicle_category->id]);
        // refresh
        $this->vehicle_category->refresh();
    }

    /** @test */
    public function we_can_create_vehicle()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();

        // Then this vehicle should be exemplar of OurVehicles class
        $this->assertTrue(get_class($vehicle) == OurVehicles::class);
    }

    /** @test */
    public function vehicle_must_have_author_relation()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();

        // Then this author relation should be exemplar of User class
        $this->assertTrue(get_class($vehicle->author) == User::class);
    }

    /** @test */
    public function vehicle_must_have_category_relation()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();

        // Then this author relation should be exemplar of User class
        $this->assertTrue(get_class($vehicle->category) == VehicleCategories::class);
    }

    /** @test */
    public function vehicle_must_have_parameters_relation()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();
        OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id]);

        // Then this author relation should be exemplar of Collection class
        $this->assertTrue(get_class($vehicle->parameters) == Collection::class);
        // And collection count should be equal to 1
        $this->assertCount(1, $vehicle->parameters);
    }

    /** @test */
    public function vehicle_must_have_parameters_relation_delete_testing()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();
        OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id]);
        $willDelete = OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id]);

        // Then collection count should be equal to 1
        $this->assertCount(2, $vehicle->parameters);

        // When we delete one parameter
        $willDelete->delete();
        // And refresh our vehicle
        $vehicle->refresh();
        // Then relation count should decrease by 1
        $this->assertCount(1, $vehicle->parameters);
    }

    /** @test */
    public function we_can_create_vehicle_parameter()
    {
        // Given fresh vehicle category characteristic
        $vehicleParameter = OurVehicleParameters::factory()->create();

        // Then this characteristic should be exemplar of VehicleCategoryCharacteristics class
        $this->assertTrue(get_class($vehicleParameter) == OurVehicleParameters::class);
    }

    /** @test */
    public function vehicle_parameter_must_have_vehicle_relation()
    {
        // Given fresh vehicle category characteristic
        $vehicleParameter = OurVehicleParameters::factory()->create();

        // Then this characteristic should be exemplar of VehicleCategoryCharacteristics class
        $this->assertTrue(get_class($vehicleParameter->vehicle) == OurVehicles::class);
    }

    /** @test */
    public function vehicle_parameter_must_have_vehicle_category_characteristic_relation()
    {
        // Given fresh vehicle category characteristic
        $vehicleParameter = OurVehicleParameters::factory()->create();

        // Then this characteristic should be exemplar of VehicleCategoryCharacteristics class
        $this->assertTrue(get_class($vehicleParameter->characteristic) == VehicleCategoryCharacteristics::class);
    }

    /** @test */
    public function a_user_from_group_with_permissions_must_have_create_permission()
    {
        // Given user from group with permissions
        $user = $this->user;

        // Then user must have permission
        $this->assertTrue($user->hasPermission('tech_acc_our_vehicle_create'));
    }

    /** @test */
    public function a_user_not_from_group_with_permissions_must_not_have_create_permission()
    {
        // Given user from group with permissions
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // Then user must have permission
        $this->assertFalse($user->hasPermission('tech_acc_our_vehicle_create'));
    }

    /** @test */
    public function we_cant_create_vehicle_by_post_without_permissions()
    {
        // Given user
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.our_vehicles.store', $this->vehicle_category->id), [
            // here nothing because this request will be stopped by authorize() method
        ]);

        // Then we must have forbidden error
        $response->assertForbidden();
    }

    /** @test */
    public function we_can_create_vehicle_by_post_with_permissions()
    {
        // Given user
        $user = $this->user;

        // When we make post request with data
        $request = [
            'number' => self::NUMBER,
            'trailer_number' => self::TRAILER_NUMBER,
            'mark' => self::MARK,
            'model' => self::MODEL,
            'owner' => self::OWNER,
            'category_id' => $this->vehicle_category->id,
        ];
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.our_vehicles.store', $this->vehicle_category->id), $request);

        // Then everything should be OK
        $response->assertOk();
        // And we must see new example in database
        $createdRow = OurVehicles::get()->last();
        $this->assertEquals($request, [
            'number' => $createdRow->number,
            'trailer_number' => $createdRow->trailer_number,
            'mark' => $createdRow->mark,
            'model' => $createdRow->model,
            'owner' => $createdRow->owner,
            'category_id' => $createdRow->category_id,
        ]);
        // And category relation should return same category, that we send
        $this->assertEquals($request['category_id'], $createdRow->category->id);
        // And user relation should return same user, that we send
        $this->assertEquals($user->id, $createdRow->author->id);
        // And parameters relation count should be equal to zero
        $this->assertCount(0, $createdRow->parameters);
    }

    /** @test */
    public function we_can_create_vehicle_by_post_include_parameters_with_permissions()
    {
        // Given user
        $user = $this->user;

        // When we make post request with data
        $request = [
            'number' => self::NUMBER,
            'trailer_number' => self::TRAILER_NUMBER,
            'mark' => self::MARK,
            'model' => self::MODEL,
            'owner' => self::OWNER,
            'category_id' => $this->vehicle_category->id,
            'parameters' => [
                [
                    'characteristic_id' => $this->vehicle_category->characteristics->first()->id,
                    'value' => 'SMTH',
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.our_vehicles.store', $this->vehicle_category->id), $request);

        // Then everything should be OK
        $response->assertOk();

        // prepare request for comparision
        $request_without_parameters = $request;
        unset($request_without_parameters['parameters']);

        // And we must see new example in database
        $createdRow = OurVehicles::get()->last();
        $this->assertEquals($request_without_parameters, [
            'number' => $createdRow->number,
            'trailer_number' => $createdRow->trailer_number,
            'mark' => $createdRow->mark,
            'model' => $createdRow->model,
            'owner' => $createdRow->owner,
            'category_id' => $createdRow->category_id,
        ]);
        // And category relation should return same category, that we send
        $this->assertEquals($request['category_id'], $createdRow->category->id);
        // And user relation should return same user, that we send
        $this->assertEquals($user->id, $createdRow->author->id);
        // And parameters relation count should be equal to one
        $this->assertCount(1, $createdRow->parameters);
        // And parameters relation should return same info, that we send
        $this->assertEquals($request['parameters'][0], [
            'characteristic_id' => $createdRow->parameters->first()->characteristic_id,
            'value' => $createdRow->parameters->first()->value,
        ]);
    }

    /** @test */
    public function we_cant_create_vehicle_category_without_number_mark_model_category_id_and_owner_by_post_with_permissions()
    {
        // Given user
        $user = $this->user;

        // When we make post request with data
        $request = [
            'number' => '',
            'trailer_number' => self::TRAILER_NUMBER,
            'mark' => '',
            'model' => '',
            'owner' => '',
            'category_id' => '',
        ];
        $response = $this->actingAs($user)->post(route('building::vehicles::vehicle_categories.our_vehicles.store', $this->vehicle_category->id), $request);

        // Then in session should be errors
        $response->assertSessionHasErrors('number');
        $response->assertSessionHasErrors('mark');
        $response->assertSessionHasErrors('model');
        $response->assertSessionHasErrors('owner');
        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function it_can_have_documents()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();

        // Given random file
        $file = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => $this->faker()->mimeType,
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => $this->user->id,
        ]);

        // When we store file entry
        $vehicle->documents()->save($file);

        // Then document relation should have count equal to one
        $this->assertCount(1, $vehicle->documents);
    }

    /** @test */
    public function we_can_delete_the_vehicle()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();

        $vehicle->delete();
        $vehicle->fresh();

        // Then vehicle must be deleted
        $this->assertTrue($vehicle->trashed());
    }

    /** @test */
    public function anyone_without_permission_cant_delete_the_vehicle()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();

        /// Given user
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // When we make post request with data
        $response = $this->actingAs($user)->delete(route('building::vehicles::vehicle_categories.our_vehicles.destroy', [$vehicle->category_id, $vehicle->id]));
        // Then we must have forbidden error
        $response->assertForbidden();
    }

    /** @test */
    public function anyone_with_permission_can_delete_the_vehicle()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();
        /// Given user
        $user = $this->user;

        // When we make post request with data
        $response = $this->actingAs($user)->delete(route('building::vehicles::vehicle_categories.our_vehicles.destroy', [$vehicle->category_id, $vehicle->id]));
        // And refresh our vehicle
        $vehicle->refresh();

        // Then vehicle should be soft deleted
        $this->assertTrue($vehicle->trashed());
    }

    /** @test */
    public function anyone_without_permission_cant_update_the_vehicle()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();

        /// Given user
        $user = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSIONS)->first();

        // When we make post request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.our_vehicles.update', [$vehicle->category_id, $vehicle->id]), [
            // no data because authorize() will stop this user
        ]);

        // Then we must have forbidden error
        $response->assertForbidden();
    }

    /** @test */
    public function we_can_update_vehicle_by_post()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();
        // Given user
        $user = $this->user;

        $request = [
            'number' => '1',
            'trailer_number' => '',
            'mark' => '3',
            'model' => '4',
            'owner' => '5',
            'category_id' => $this->vehicle_category->id,
        ];

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.our_vehicles.update', [$vehicle->category_id, $vehicle->id]), $request);
        // And refresh our category
        $vehicle->refresh();

        // Then everything should be OK
        $response->assertOk();
        // And our vehicle must change
        $this->assertEquals($request, [
            'number' => $vehicle->number,
            'trailer_number' => $vehicle->trailer_number,
            'mark' => $vehicle->mark,
            'model' => $vehicle->model,
            'owner' => $vehicle->owner,
            'category_id' => $vehicle->category_id,
        ]);
    }

    /** @test */
    public function we_can_update_vehicle_by_post_include_parameters()
    {
        // Given fresh vehicle with parameter
        $vehicle = OurVehicles::factory()->create();
        $parameter = OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id, 'characteristic_id' => $vehicle->category->characteristics->first()->id]);
        // Given user
        $user = $this->user;

        $request = [
            'number' => '1',
            'trailer_number' => '',
            'mark' => '3',
            'model' => '4',
            'owner' => '5',
            'category_id' => $this->vehicle_category->id,
            'parameters' => [
                [
                    'id' => $parameter->id,
                    'characteristic_id' => $parameter->characteristic_id,
                    'value' => '',
                ],
            ],
        ];

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.our_vehicles.update', [$vehicle->category_id, $vehicle->id]), $request);
        // And refresh our vehicle
        $vehicle->refresh();
        // And refresh our parameter
        $parameter->refresh();

        $request_without_parameters = $request;
        unset($request_without_parameters['parameters']);
        // Then everything should be OK
        $response->assertOk();
        // And our vehicle must change
        $this->assertEquals($request_without_parameters, [
            'number' => $vehicle->number,
            'trailer_number' => $vehicle->trailer_number,
            'mark' => $vehicle->mark,
            'model' => $vehicle->model,
            'owner' => $vehicle->owner,
            'category_id' => $vehicle->category_id,
        ]);

        // And our vehicle must change
        $this->assertEquals($request['parameters'][0], [
            'id' => $parameter->id,
            'characteristic_id' => $parameter->characteristic_id,
            'value' => $parameter->value,
        ]);
    }

    /** @test */
    public function we_can_update_vehicle_by_post_without_parameters_id()
    {
        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create();
        // Given user
        $user = $this->user;

        $request = [
            'number' => '1',
            'trailer_number' => '',
            'mark' => '3',
            'model' => '4',
            'owner' => '5',
            'category_id' => $this->vehicle_category->id,
            'parameters' => [
                [
                    'id' => '',
                    'value' => '',
                ],
            ],
        ];

        // When we make put request with data
        $response = $this->actingAs($user)->put(route('building::vehicles::vehicle_categories.our_vehicles.update', [$vehicle->category_id, $vehicle->id]), $request);

        // Then response should have errors
        $response->assertSessionDoesntHaveErrors('parameters.*.id');
    }

    /** @test */
    public function vehicle_deleting_influence()
    {
        // Given fresh vehicle category with characteristic
        $vehicleCategory = VehicleCategories::factory()->create();
        $characteristic = VehicleCategoryCharacteristics::factory()->create(['show' => 1, 'category_id' => $vehicleCategory->id]);
        // Given fresh vehicle with parameter
        $vehicle = OurVehicles::factory()->create(['category_id' => $vehicleCategory->id]);
        $parameter = OurVehicleParameters::factory()->create(['vehicle_id' => $vehicle->id, 'characteristic_id' => $characteristic->id]);

        // When we delete vehicle
        $vehicle->delete();

        // Refresh models
        $vehicleCategory->refresh();
        $characteristic->refresh();
        $vehicle->refresh();
        $parameter->refresh();

        // Then vehicle should be deleted
        $this->assertTrue($vehicle->trashed());
        // Vehicle parameters should be deleted - UPD NOW NOT
        //        $this->assertTrue($parameter->trashed());
    }

    /** @test */
    public function it_collect_full_name_as_text()
    {
        $vehicleCategory = VehicleCategories::factory()->create([
            'name' => 'Fastest car EVER',
        ]);

        // Given fresh vehicle
        $vehicle = OurVehicles::factory()->create([
            'number' => self::NUMBER,
            'trailer_number' => self::TRAILER_NUMBER,
            'mark' => self::MARK,
            'model' => self::MODEL,
            'category_id' => $vehicleCategory->id,
        ]);

        $expected_text = "{$vehicleCategory->name} {$vehicle->mark} {$vehicle->model} {$vehicle->number} {$vehicle->trailer_number}";

        $this->assertEquals($expected_text, $vehicle->full_name);
    }
}

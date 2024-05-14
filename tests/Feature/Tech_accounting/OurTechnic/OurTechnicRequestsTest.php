<?php

namespace Tests\Feature;

use App\Models\FileEntry;
use App\Models\Permission;
use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OurTechnicRequestsTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $ivan;

    protected $boss;

    public function setUp(): void
    {
        parent::setUp();

        OurTechnic::query()->delete();
        $this->ivan = User::first();
        $this->boss = User::find(6);
        $permissions = Permission::whereIn('codename', ['tech_acc_tech_category_delete', 'tech_acc_tech_category_update', 'tech_acc_tech_category_create'])->get();

        $this->boss->user_permissions()->attach($permissions->pluck('id'));
        $this->actingAs($this->boss);
        Storage::fake('technics');
        $this->withExceptionHandling();

    }

    /** @test */
    public function user_can_see_technics_index_page()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();
        $category_characteristic = factory(CategoryCharacteristic::class)->create();
        $category->addCharacteristic($category_characteristic);

        $this->get(route('building::tech_acc::technic_category.our_technic.index', $category->id))
            ->assertSee('Список техники');
    }

    /** @test */ //index
    public function it_gets_data_about_all_technic_created()
    {
        $category = factory(TechnicCategory::class)->create();
        $category_characteristics = factory(CategoryCharacteristic::class, 2)->create();

        $category->addCharacteristic($category_characteristics->pluck('id'));

        $technic = factory(OurTechnic::class)->create(['technic_category_id' => $category->id]);

        $characteristics_data = [
            [
                'id' => $category_characteristics->first()->id,
                'value' => '80',
            ],
            [
                'id' => $category_characteristics->last()->id,
                'value' => 'fast',
            ],
        ];

        $technic->setCharacteristicsValue($characteristics_data);

        $file = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => $this->faker()->mimeType,
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => 1,
        ]);

        $technic->documents()->save($file);
        $this->actingAs($this->ivan);

        $data = $this->get(route('building::tech_acc::technic_category.our_technic.index', $category->id))
            ->viewData('data');

        $this->assertEquals($characteristics_data[0]['value'], $data['technics'][0]->category_characteristics->first()->data->value);
        $this->assertEquals($file->filename, $data['technics'][0]->documents->first()->filename);
    }

    /** @test */ //store
    public function user_can_create_technic()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();

        $this->post(route('building::tech_acc::technic_category.our_technic.store', $category->id), $this->validFields(['technic_category_id' => $category->id]))
            ->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $this->assertDatabaseHas('our_technics', ['exploitation_start' => \Carbon\Carbon::parse('22.11.2019')]);
    }

    /** @test */ //store
    public function it_accepts_category_name_as_attribute()
    {
        $this->actingAs($this->ivan);

        $category = factory(TechnicCategory::class)->create();

        $this->post(route('building::tech_acc::technic_category.our_technic.store', $category->id), $this->validFields(['technic_category_id' => $category->id]))
            ->assertSee('success');

        $this->assertDatabaseHas('our_technics', ['technic_category_id' => $category->id]);
    }

    /** @test */ //store
    public function user_can_create_technic_with_characteristics()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();

        $category_characteristics = factory(CategoryCharacteristic::class, 2)->create();

        $this->post(route('building::tech_acc::technic_category.our_technic.store', $category->id), $this->validFields([
            'brand' => 'Ford',
            'technic_category_id' => $category->id,
            'characteristics' => [
                [
                    'id' => $category_characteristics->first()->id,
                    'value' => '80',
                ],
                [
                    'id' => $category_characteristics->last()->id,
                    'value' => 'fast',
                ],
            ],
        ]))->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $brand_new_our_technic = OurTechnic::first();

        $this->assertDatabaseHas('our_technics', ['brand' => 'Ford']);
        $this->assertEquals('80', $brand_new_our_technic->category_characteristics->first()->data->value);
    }

    /** @test */ //store
    public function user_can_also_attach_files()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();
        $file_entries = factory(FileEntry::class, 2)->create();

        $technic_brand = $this->faker()->words(2, true);

        $this->post(route('building::tech_acc::technic_category.our_technic.store', $category->id),
            $this->validFields([
                'brand' => $technic_brand,
                'technic_category_id' => $category->id,
                'file_ids' => $file_entries->pluck('id'),
            ]))->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $brand_new_technic = OurTechnic::where('brand', $technic_brand)->first();

        $this->assertCount(2, $brand_new_technic->documents);
        $this->assertEquals($file_entries->last()->original_filename, $brand_new_technic->documents->last()->original_filename);
    }

    /** @test */ //store
    public function server_send_stored_model_in_response()
    {
        $this->actingAs($this->ivan);

        $category = factory(TechnicCategory::class)->create();

        $technic_brand = $this->faker()->words(2, true);

        $response = $this->post(route('building::tech_acc::technic_category.our_technic.store', $category->id),
            $this->validFields([
                'brand' => $technic_brand,
                'technic_category_id' => $category->id,
            ]));

        $this->assertEquals($technic_brand, json_decode($response->getContent())->data->brand);

        $this->assertDatabaseHas('our_technics', ['technic_category_id' => $category->id]);
    }

    /** @test */ //destroy
    public function user_can_destroy_technic()
    {
        $this->actingAs($this->ivan);
        $our_technic = factory(OurTechnic::class)->create();

        $this->delete(route('building::tech_acc::technic_category.our_technic.destroy', [$our_technic->technic_category_id, $our_technic->id]))
            ->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $this->assertSoftDeleted($our_technic);
    }

    /** @test */ //destroy
    public function getter_does_not_return_soft_deleted_models()
    {
        $this->actingAs($this->ivan);
        $our_technic = factory(OurTechnic::class, 10)->create();

        $this->delete(route('building::tech_acc::technic_category.our_technic.destroy', [$our_technic->first()->technic_category_id, $our_technic->first()->id]))
            ->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $response = $this->get(route('building::tech_acc::get_technics'))->assertStatus(200);

        $this->assertCount($our_technic->count() - 1, $response->json('data'));
    }

    /** @test */ //update
    public function user_can_update_technic_with_characteristics_and_documents()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();
        $category_characteristics = factory(CategoryCharacteristic::class, 2)->create();
        $category->addCharacteristic($category_characteristics->pluck('id'));
        $technic = factory(OurTechnic::class)->create(['technic_category_id' => $category->id]);

        $characteristics_value = [];

        foreach ($category_characteristics as $characteristic) {
            $characteristics_value[] = [
                'id' => $characteristic->id,
                'value' => $this->faker()->randomNumber(4),
            ];
        }
        $technic->setCharacteristicsValue($characteristics_value);
        $file_entries = factory(FileEntry::class, 2)->create();

        //all these is needed to create technic

        $this->assertDatabaseHas('our_technics', ['model' => $technic->model]);
        $this->assertDatabaseHas('category_characteristic_technic', ['value' => $technic->category_characteristics->last()->data->value]);

        $technic_model = $this->faker()->words(4, true);
        $response = $this->put(route('building::tech_acc::technic_category.our_technic.update', [$category->id, $technic->id]), [
            'model' => $technic_model,
            'characteristics' => [
                [
                    'id' => "{$category_characteristics->last()->id}",
                    'value' => 'fast',
                ],
            ],
            'file_ids' => $file_entries->pluck('id'),
        ])->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $technic->refresh();

        $this->assertEquals($technic_model, json_decode($response->getContent())->data->model);
        $this->assertEquals($technic->category_characteristics->last()->data->value, 'fast');

        $this->assertDatabaseHas('our_technics', ['model' => $technic_model]);
        $this->assertDatabaseHas('category_characteristic_technic', ['value' => 'fast']);

        $this->assertCount(2, $technic->documents);
        $this->assertEquals($file_entries->last()->original_filename, $technic->documents->last()->original_filename);
    }

    /** @test */ //destroy document
    public function user_can_delete_technic_document()
    {
        $technic = factory(OurTechnic::class)->create();

        $file = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => $this->faker()->mimeType,
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => 1,
        ]);

        $technic->documents()->save($file);

        $this->delete(route('file_entry.destroy', $file->id))
            ->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $this->assertCount(0, $technic->documents);
    }

    /** @test */ //store request
    public function it_requires_model_for_technic()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();

        $this->post(route('building::tech_acc::technic_category.our_technic.store', $category->id),
            $this->validFields(['model' => ''])
        )->assertSessionHasErrors('model');
    }

    /** @test */ //store
    public function it_validates_characteristics()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();

        $category_characteristics = factory(CategoryCharacteristic::class, 2)->create();

        $brand_new_our_technic = factory(OurTechnic::class)->create(['technic_category_id' => $category->id]);

        $request = $this->validFields([
            'characteristics' => [
                [
                    'id' => 'hi',
                    'value' => 'fast',
                ],
            ],
        ]);

        $this->put(route('building::tech_acc::technic_category.our_technic.update', [$category->id, $brand_new_our_technic->id]), $request)
            ->assertSessionHasErrors('characteristics.*.id')
            ->assertStatus(302);
    }

    /** @test */
    public function it_parse_date_correctly()
    {
        $this->actingAs($this->ivan);
        $category = factory(TechnicCategory::class)->create();

        $this->post(route('building::tech_acc::technic_category.our_technic.store', $category->id), [
            'brand' => 'Kia',
            'model' => 'WithoutChars',
            'owner' => 'ООО СК ГОРОД',
            'start_location_id' => '1',
            'technic_category_id' => $category->id,
            'exploitation_start' => '21.11.2019',
            'inventory_number' => $this->faker()->randomNumber(5),
        ])->assertSessionDoesntHaveErrors()
            ->assertSee('success');

        $this->assertDatabaseHas('our_technics', ['exploitation_start' => \Carbon\Carbon::parse('21.11.2019')]);
    }

    /** @test */
    public function it_returns_all_technics_found_by_name()
    {
        $technics = factory(OurTechnic::class, 10)->create();
        $technics->merge(factory(OurTechnic::class, 5)->create(['brand' => 'My_new_brand']));

        $response = $this->get(route('building::tech_acc::get_technics'))->assertSessionDoesntHaveErrors();
        $this->assertEquals($response->json('data')[0]['model'], $technics->first()->model);

        $another_response = $this->get(route('building::tech_acc::get_technics', ['q' => 'My_new_brand']))->assertSessionDoesntHaveErrors();
        $this->assertCount(5, $another_response->json('data'));
    }

    protected function validFields($overrides = [])
    {
        return array_merge([
            'brand' => 'Kia',
            'model' => 'WithFourWeels',
            'owner' => 'ООО СК ГОРОД',
            'start_location_id' => '1',
            'technic_category_id' => factory(TechnicCategory::class)->create()->id,
            'exploitation_start' => '22.11.2019',
            'inventory_number' => $this->faker()->randomNumber(5),
        ], $overrides);
    }
}

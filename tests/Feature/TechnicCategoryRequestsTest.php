<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TechnicCategoryRequestsTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $ivan;

    protected $boss;

    protected function setUp(): void
    {
        parent::setUp();

        TechnicCategory::query()->delete();
        OurTechnic::query()->delete();

        $this->ivan = User::first();
        $this->boss = User::find(6);
        $permissions = Permission::whereIn('codename', ['tech_acc_tech_category_delete', 'tech_acc_tech_category_update', 'tech_acc_tech_category_create'])->get();

        $this->boss->user_permissions()->attach($permissions->pluck('id'));
        $this->actingAs($this->boss);
    }

    /** @test */
    public function user_without_permissions_sees_nothing_but_index()
    {
        $category = factory(TechnicCategory::class)->create();
        $this->actingAs($this->ivan);

        $this->get(route('building::tech_acc::technic_category.create'))
            ->assertDontSee('Создание категории');

        $this->get(route('building::tech_acc::technic_category.edit', $category->id))
            ->assertDontSee($category->name);

        $this->get(route('building::tech_acc::technic_category.index'))
            ->assertSee($category->name);
    }

    /** @test */
    public function user_with_permissions_can_see_technic_category_pages()
    {
        $category = factory(TechnicCategory::class)->create();

        $this->get(route('building::tech_acc::technic_category.create'))
            ->assertSee('Создание категории');
        //      edit is not implemented yet
        //        $this->get(route('building::tech_acc::technic_category.edit', $category->id))
        //            ->assertSee($category->name);

        $this->get(route('building::tech_acc::technic_category.index'))
            ->assertSee($category->name);
    }

    /** @test */
    public function user_can_see_edit_technic_category_page()
    {
        $technic = factory(TechnicCategory::class)->create();

        $this->get(route('building::tech_acc::technic_category.edit', $technic->id))
            ->assertSee('Редактирование категории');

    }

    /** @test */
    public function user_can_create_technic_without_characteristics()
    {
        $category_name = $this->faker()->words(3, true);

        $this->postJson(route('building::tech_acc::technic_category.store'), [
            'name' => $category_name,
            'description' => $this->faker()->sentence(),
        ])->assertSee('success');

        $this->assertDatabaseHas('technic_categories', ['name' => $category_name]);
    }

    /** @test */
    public function user_can_submit_form_and_technic_would_be_created_with_characteristics()
    {
        $category_name = $this->faker()->words(3, true);
        $characteristic_name = $this->faker()->words(3, true);

        $this->postJson(route('building::tech_acc::technic_category.store'), [
            'characteristics' => [
                [
                    'name' => $characteristic_name,
                    'unit' => $this->faker()->word(),
                    'description' => $this->faker()->sentence(),
                    'is_hidden' => $this->faker()->numberBetween(0, 1),
                    'required' => $this->faker()->numberBetween(0, 1),
                ],
            ],
            'name' => $category_name,
            'description' => $this->faker()->sentence(),
        ]);

        $this->assertDatabaseHas('technic_categories', ['name' => $category_name]);
        $this->assertDatabaseHas('category_characteristics', ['name' => $characteristic_name]);

        $this->assertEquals($characteristic_name, TechnicCategory::where('name', $category_name)->get()->last()->category_characteristics()->first()->name);
    }

    /** @test */
    public function it_checks_that_all_attributes_are_present_and_forbid_to_store_model()
    {
        $this->postJson(route('building::tech_acc::technic_category.store'), [
            'characteristics' => [
                [
                    //                        'name' => $this->faker()->words(3, true), //comment it to get an error
                    'unit' => $this->faker()->word(),
                    'description' => $this->faker()->sentence(),
                    'is_hidden' => $this->faker()->numberBetween(0, 1),
                ],
            ],
            'name' => $this->faker()->words(3, true),
            'description' => $this->faker()->sentence(),
        ])
            ->assertSee('errors');

        $this->assertDatabaseMissing('technic_categories', ['description' => $this->faker()->sentence()]);

    }

    /** @test */
    public function user_can_delete_technic_category()
    {
        $technic = factory(TechnicCategory::class, 2)->create();

        $this->deleteJson(route('building::tech_acc::technic_category.destroy', $technic->first()->id))
            ->assertSee('success');

        $this->assertSoftDeleted($technic->first());
        $this->assertCount(1, TechnicCategory::get());
    }

    /** @test */
    public function after_deleting_a_category_technics_also_have_to_be_deleted()
    {
        $technic_category = factory(TechnicCategory::class)->create();

        $technic = factory(OurTechnic::class, 5)->create(['technic_category_id' => $technic_category->id]);

        $this->deleteJson(route('building::tech_acc::technic_category.destroy', $technic_category->id))
            ->assertSee('success');

        $this->assertSoftDeleted($technic_category);
        $this->assertCount(0, OurTechnic::all());
    }

    /** @test */
    public function user_can_edit_attributes_of_a_category()
    {
        $old_category = factory(TechnicCategory::class)->create();
        $characteristic = factory(CategoryCharacteristic::class)->create();

        $old_category->addCharacteristic($characteristic);

        $category_new_name = $this->faker()->words(3, true);
        $characteristic_new_name = $this->faker()->words(3, true);

        $this->putJson(route('building::tech_acc::technic_category.update', $old_category->first()->id), [
            'characteristics' => [
                [
                    'id' => $old_category->category_characteristics->first()->id,
                    'name' => $characteristic_new_name,
                    'unit' => $this->faker()->word(),
                    'description' => $this->faker()->sentence(),
                    'is_hidden' => $this->faker()->numberBetween(0, 1),
                    'required' => $this->faker()->numberBetween(0, 1),
                ],
            ],
            'name' => $category_new_name,
            'description' => $old_category->description,
        ])->assertSee('success');

        $this->assertNotEquals($old_category->name, TechnicCategory::first()->name);
        $this->assertNotEquals($characteristic->name, TechnicCategory::first()->category_characteristics->first()->name);
        $this->assertEquals($old_category->description, TechnicCategory::first()->description);
    }

    /** @test */
    public function user_can_add_characteristics_to_a_category_by_update()
    {
        $old_category = factory(TechnicCategory::class)->create();
        $characteristic = factory(CategoryCharacteristic::class)->create();

        $old_category->addCharacteristic($characteristic);

        $category_new_name = $this->faker()->words(3, true);
        $characteristic_new_name = $this->faker()->words(3, true);
        $another_characteristic_name = $this->faker()->words(3, true);

        $this->putJson(route('building::tech_acc::technic_category.update', $old_category->first()->id), [
            'characteristics' => [
                [
                    'id' => $old_category->category_characteristics->first()->id,
                    'name' => $characteristic_new_name,
                    'unit' => $this->faker()->word(),
                    'description' => $this->faker()->sentence(),
                    'is_hidden' => $this->faker()->numberBetween(0, 1),
                    'required' => $this->faker()->numberBetween(0, 1),
                ],
                [
                    'id' => 0,
                    'name' => $another_characteristic_name,
                    'unit' => $this->faker()->word(),
                    'description' => $this->faker()->sentence(),
                    'is_hidden' => $this->faker()->numberBetween(0, 1),
                    'required' => $this->faker()->numberBetween(0, 1),
                ],
            ],
            'name' => $category_new_name,
            'description' => $old_category->description,
        ])->assertSee('success');

        $this->assertNotEquals($old_category->name, TechnicCategory::first()->name);
        $this->assertNotEquals($characteristic->name, TechnicCategory::first()->category_characteristics->first()->name);
        $this->assertCount(1, TechnicCategory::first()->category_characteristics()->where('name', $another_characteristic_name)->get());
        $this->assertCount(2, TechnicCategory::first()->category_characteristics()->get());
        $this->assertEquals($old_category->description, TechnicCategory::first()->description);
    }

    /** @test */
    public function user_can_delete_one_characteristic_from_a_category_by_update()
    {
        $technic = factory(TechnicCategory::class)->create();
        $characteristic = factory(CategoryCharacteristic::class, 2)->create();
        $technic->addCharacteristic($characteristic);

        $this->putJson(route('building::tech_acc::technic_category.update', $technic->first()->id), [
            'deleted_characteristic_ids' => [
                $characteristic->last()->id,
            ],
            'name' => $technic->name,
            'description' => $technic->description,
        ])->assertSee('success');

        $this->assertEquals($technic->name, TechnicCategory::first()->name);
        $this->assertCount(1, TechnicCategory::first()->category_characteristics()->get());
        $this->assertEquals($technic->description, TechnicCategory::first()->description);
    }
}

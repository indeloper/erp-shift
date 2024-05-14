<?php

namespace Tests\Feature;

use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class TechnicCategoryTest extends TestCase
{
    use WithoutMiddleware;

    protected $technic;

    protected $ivan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->technic = factory(TechnicCategory::class)->create();
        $this->ivan = User::first();
    }

    /** @test */
    public function it_can_add_category_characteristic()
    {
        $characteristic = factory(CategoryCharacteristic::class)->create();

        $this->technic->addCharacteristic($characteristic);

        $this->assertCount(1, $this->technic->category_characteristics);
    }

    /** @test */
    public function it_can_add_multiple_category_characteristics()
    {
        $characteristics = factory(CategoryCharacteristic::class, 2)->create();

        $this->technic->addCharacteristic($characteristics);

        $this->assertCount(2, $this->technic->category_characteristics);
    }

    /** @test */
    public function it_returns_only_its_technics()
    {
        $technics = factory(OurTechnic::class, 4)->create(['technic_category_id' => $this->technic->id]);

        $technics_from_another_categories = factory(OurTechnic::class, 4)->create(['technic_category_id' => factory(TechnicCategory::class)->create()->id]);

        $this->assertEquals($technics->pluck('id')->sort(), $this->technic->technics->pluck('id')->sort());
    }
}

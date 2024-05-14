<?php

namespace Tests\Feature\Tech_accounting\OurTechnic;

use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\TechnicCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryCharacteristicTest extends TestCase
{
    use DatabaseTransactions;

    protected $characteristic;

    public function setUp(): void
    {
        parent::setUp();

        $this->characteristic = factory(CategoryCharacteristic::class)->create();
    }

    /** @test */
    public function this_can_have_own_set_of_technic_category()
    {
        $firstCharacteristic = factory(CategoryCharacteristic::class)->create();
        $secondCharacteristic = factory(CategoryCharacteristic::class)->create();

        $technic = factory(TechnicCategory::class)->create();

        $technic->addCharacteristic([$firstCharacteristic, $secondCharacteristic]);

        $this->assertEquals($firstCharacteristic->technic_categories()->count(), $secondCharacteristic->technic_categories()->count());

        // different technic not equal

        $anotherTechnic = factory(TechnicCategory::class)->create();

        $anotherTechnic->addCharacteristic($firstCharacteristic);

        $this->assertNotEquals($firstCharacteristic->technic_categories()->count(), $secondCharacteristic->technic_categories()->count());
    }
}

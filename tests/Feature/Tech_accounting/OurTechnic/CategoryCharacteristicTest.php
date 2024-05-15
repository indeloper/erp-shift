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

    protected function setUp(): void
    {
        parent::setUp();

        $this->characteristic = CategoryCharacteristic::factory()->create();
    }

    /** @test */
    public function this_can_have_own_set_of_technic_category()
    {
        $firstCharacteristic = CategoryCharacteristic::factory()->create();
        $secondCharacteristic = CategoryCharacteristic::factory()->create();

        $technic = TechnicCategory::factory()->create();

        $technic->addCharacteristic([$firstCharacteristic, $secondCharacteristic]);

        $this->assertEquals($firstCharacteristic->technic_categories()->count(), $secondCharacteristic->technic_categories()->count());

        // different technic not equal

        $anotherTechnic = TechnicCategory::factory()->create();

        $anotherTechnic->addCharacteristic($firstCharacteristic);

        $this->assertNotEquals($firstCharacteristic->technic_categories()->count(), $secondCharacteristic->technic_categories()->count());
    }
}

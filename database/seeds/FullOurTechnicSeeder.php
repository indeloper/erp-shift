<?php

use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use Illuminate\Database\Seeder;

class FullOurTechnicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = factory(TechnicCategory::class)->create();
        $category_characteristics = factory(CategoryCharacteristic::class, 2)->create();

        $category->addCharacteristic($category_characteristics->pluck('id'));

        $technics = factory(OurTechnic::class, 3)->create(['technic_category_id' => $category->id]);

        $technics->each(function($technic) use ($category_characteristics) {
            $technic->setCharacteristicsValue([
                [
                    'id' => $category_characteristics->first()->id,
                    'value' => \Faker\Factory::create('ru_RU')->randomNumber(3),
                ],
                [
                    'id' => $category_characteristics->last()->id,
                    'value' => \Faker\Factory::create('ru_RU')->word,
                ],
            ]);
        });

    }
}

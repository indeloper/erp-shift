<?php

namespace Database\Factories\TechAcc;

use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class OurTechnicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'brand' => $this->faker->word,
            'model' => $this->faker->word.$this->faker->randomNumber(3),
            'owner' => OurTechnic::$owners[array_rand(OurTechnic::$owners)],
            'start_location_id' => ProjectObject::count() ? ProjectObject::inRandomOrder()->first()->id : ProjectObject::factory()->create()->id,
            'technic_category_id' => TechnicCategory::count() ? TechnicCategory::inRandomOrder()->first()->id : TechnicCategory::factory()->create()->id,
            'exploitation_start' => \Carbon\Carbon::now()->subDays(60),
            'inventory_number' => $this->faker->randomNumber(5),
        ];
    }
}

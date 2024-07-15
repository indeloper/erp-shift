<?php

namespace Database\Factories\TechAcc;

use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OurTechnicTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        if (! ProjectObject::count()) {
            ProjectObject::factory()->count(6)->create();
        }
        if (! OurTechnic::count()) {
            $technic_id = OurTechnic::factory()->create();
        } else {
            $technic_id = OurTechnic::first()->id;
        }

        $sending_from_date = isset($attributes['sending_from_date']) ? Carbon::parse($attributes['sending_from_date']) : Carbon::now();
        $sending_to_date = isset($attributes['sending_to_date']) ? Carbon::parse($attributes['sending_to_date']) : $sending_from_date->addDays(2);
        $getting_from_date = isset($attributes['getting_from_date']) ? Carbon::parse($attributes['getting_from_date']) : $sending_to_date->addDays(2);
        $getting_to_date = isset($attributes['getting_to_date']) ? Carbon::parse($attributes['getting_to_date']) : $getting_from_date->addDays(3);
        $usage_from_date = isset($attributes['usage_from_date']) ? Carbon::parse($attributes['usage_from_date']) : $getting_to_date;
        $usage_to_date = isset($attributes['usage_to_date']) ? Carbon::parse($attributes['usage_to_date']) : $usage_from_date->addWeek();

        return [
            'our_technic_id' => $technic_id,
            'sending_object_id' => ProjectObject::first(),
            'getting_object_id' => ProjectObject::take(5)->get()->random(),
            'usage_days' => $this->faker->randomNumber(2),
            'sending_from_date' => $sending_from_date,
            'sending_to_date' => $sending_to_date,
            'getting_from_date' => $getting_from_date,
            'getting_to_date' => $getting_to_date,
            'usage_from_date' => $usage_from_date,
            'usage_to_date' => $usage_to_date,
            'comment' => $this->faker->text(150),
            'status' => 1,
            'type' => 3,
        ];
    }
}

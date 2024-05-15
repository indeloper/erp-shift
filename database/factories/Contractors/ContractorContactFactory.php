<?php

namespace Database\Factories\Contractors;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorContact;

class ContractorContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'phone_number' => random_int(89000000000, 89999999999),
            'position' => $this->faker->word,
            'contractor_id' => Contractor::inRandomOrder()->first()->id,
        ];
    }
}

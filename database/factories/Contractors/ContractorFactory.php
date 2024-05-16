<?php

namespace Database\Factories\Contractors;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContractorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'full_name' => $this->faker->company,
            'short_name' => $this->faker->company,
            'inn' => random_int(89000000000, 89999999999),
            'legal_address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
            'main_type' => null,
        ];
    }
}

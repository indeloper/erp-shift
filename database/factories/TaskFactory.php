<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->text(50),
            'project_id' => null,
            'contractor_id' => null,
            'user_id' => null,
            'responsible_user_id' => null,
            'contact_id' => null,
            'incoming_phone' => null,
            'internal_phone' => null,
            'expired_at' => now()->addHours(8),
            'final_note' => null,
            'is_solved' => 0,
            'status' => 1,
            'is_seen' => 0,
        ];
    }
}

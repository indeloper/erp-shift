<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'filename' => $this->faker->words(4, true),
            'size' => $this->faker->randomNumber(4),
            'mime' => $this->faker->mimeType(),
            'original_filename' => $this->faker->word().$this->faker->fileExtension(),
            'user_id' => User::first()->id,
        ];
    }
}

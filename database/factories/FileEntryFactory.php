<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FileEntry;
use App\Models\User;

class FileEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'filename' => $this->faker->words(4, true),
            'size' => $this->faker->randomNumber(4),
            'mime' => $this->faker->mimeType,
            'original_filename' => $this->faker->word.$this->faker->fileExtension,
            'user_id' => User::first()->id,
        ];
    }
}

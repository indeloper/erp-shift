<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupportMail;
use App\Models\User;

class SupportMailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'user_id' => User::factory()->create()->id,
            'page_path' => $this->faker->url,
            'status' => 'new',
        ];
    }
}

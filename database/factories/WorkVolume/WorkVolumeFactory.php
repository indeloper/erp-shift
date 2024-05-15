<?php



namespace Database\Factories\WorkVolume;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;

class WorkVolumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'project_id' => Project::inRandomOrder()->first()->id ?? Project::factory(),
            'type' => rand(0, 1),
        ];
    }
}

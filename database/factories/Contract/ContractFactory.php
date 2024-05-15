<?php



namespace Database\Factories\Contract;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contract\Contract;
use App\Models\Project;
use App\Models\User;

class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $passedAttributes = func_get_arg(1);

        return [
            'project_id' => function () use ($passedAttributes) {
                if (! in_array('project_id', $passedAttributes)) {
                    return Project::factory()->create()->id;
                }
            },
            'name' => $this->faker->colorName,
            'user_id' => function () use ($passedAttributes) {
                if (! in_array('user_id', $passedAttributes)) {
                    return User::factory()->create()->id;
                }
            },
            'status' => 1,
            'version' => 1,
            'contract_id' => random_int(0, 100),
        ];
    }
}

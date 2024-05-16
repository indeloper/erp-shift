<?php

namespace Database\Factories\TechAcc\FuelTank;

use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FuelTankOperationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $users = User::active()->whereIn('group_id', array_merge(Group::PROJECT_MANAGERS, Group::FOREMEN))->inRandomOrder()->first();
        $contractor = Contractor::count() ? Contractor::inRandomOrder()->first() : Contractor::factory()->create();

        $type = $this->faker->randomElement([1, 2]);

        $fuel_tank = FuelTank::inRandomOrder()->first();
        if (! $fuel_tank) {
            $fuel_tank = FuelTank::factory()->create();
        }

        $ourTechnic = OurTechnic::inRandomOrder()->first();
        if (! $ourTechnic) {
            $ourTechnic = OurTechnic::factory()->create();
        }

        return [
            'fuel_tank_id' => $fuel_tank->id,
            'author_id' => $users->id,
            'object_id' => ProjectObject::inRandomOrder()->first()->id,
            'our_technic_id' => $type == 2 ? $ourTechnic->id : '',
            'contractor_id' => $type == 1 ? $contractor->id : '',
            'value' => $this->faker->randomFloat(3, 0, 300),
            'type' => $type,
            'description' => $this->faker->text(),
            'operation_date' => \Carbon\Carbon::now(),
            'owner_id' => 1,
        ];
    }

    public function outgo()
    {
        return $this->state(function () {
            return [
                'our_technic_id' => OurTechnic::factory()->create(),
                'contractor_id' => '',
                'type' => 2,
            ];
        });
    }

    public function income()
    {
        return $this->state(function () {
            $contractor = Contractor::count() ? Contractor::inRandomOrder()->first() : Contractor::factory()->create();

            return [
                'contractor_id' => $contractor->id,
                'our_technic_id' => '',
                'type' => 1,
            ];
        });
    }

    public function manual()
    {
        return $this->state(function () {
            return [
                'contractor_id' => '',
                'our_technic_id' => '',
                'type' => 3,
            ];
        });
    }
}

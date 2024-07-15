<?php

namespace Database\Factories\MatAcc;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialAccountingOperationResponsibleUsersFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $passedAttributes = func_get_arg(1);

        return [
            'operation_id' => function () use ($passedAttributes) {
                if (! in_array('operation_id', $passedAttributes)) {
                    return MaterialAccountingOperation::factory()->create()->id;
                }
            },
            'user_id' => function () use ($passedAttributes) {
                if (! in_array('user_id', $passedAttributes)) {
                    return User::factory()->create()->id;
                }
            },
        ];
    }
}

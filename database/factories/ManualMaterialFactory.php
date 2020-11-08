<?php

use Faker\Generator as Faker;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;

$factory->define(ManualMaterial::class, function (Faker $faker) {
    $names = ['Камень', 'Кирпич', 'Арматура', 'Шпунт', 'Свая', 'Крепёж', 'Электрод', 'Гайка', 'Болт', 'Гайка', 'Проволока'];

    return [
        'name' => $names[random_int(0, count($names) - 1)],
        'description' => $faker->sentence(2, true),
        'category_id' => ManualMaterialCategory::inRandomOrder()->first()->id,
        'buy_cost' => random_int(50, 500),
        'use_cost' => random_int(50, 500)
    ];
});

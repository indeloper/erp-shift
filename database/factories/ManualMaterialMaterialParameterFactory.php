<?php

use Faker\Generator as Faker;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialParameter;

$factory->define(ManualMaterialParameter::class, function (Faker $faker) {
    $mat = ManualMaterial::inRandomOrder()->first();
    return [
        'attr_id' => ManualMaterialCategory::find($mat->category_id)->attributes()->inRandomOrder()->first()->id,
        'mat_id' => $mat->id,
        'value' => random_int(1, 50),
    ];
});

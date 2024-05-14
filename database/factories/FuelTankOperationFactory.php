<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(FuelTankOperation::class, function (Faker $faker) {
    $users = User::active()->whereIn('group_id', array_merge(Group::PROJECT_MANAGERS, Group::FOREMEN))->inRandomOrder()->first();
    $contractor = Contractor::count() ? Contractor::inRandomOrder()->first() : factory(Contractor::class)->create();

    $type = $faker->randomElement([1, 2]);

    $fuel_tank = FuelTank::inRandomOrder()->first();
    if (! $fuel_tank) {
        $fuel_tank = factory(FuelTank::class)->create();
    }

    $ourTechnic = OurTechnic::inRandomOrder()->first();
    if (! $ourTechnic) {
        $ourTechnic = factory(OurTechnic::class)->create();
    }

    return [
        'fuel_tank_id' => $fuel_tank->id,
        'author_id' => $users->id,
        'object_id' => ProjectObject::inRandomOrder()->first()->id,
        'our_technic_id' => $type == 2 ? $ourTechnic->id : '',
        'contractor_id' => $type == 1 ? $contractor->id : '',
        'value' => $faker->randomFloat(3, 0, 300),
        'type' => $type,
        'description' => $faker->text(),
        'operation_date' => \Carbon\Carbon::now(),
        'owner_id' => 1,
    ];
});

$factory->state(FuelTankOperation::class, 'outgo', function ($faker) {
    return [
        'our_technic_id' => factory(OurTechnic::class)->create(),
        'contractor_id' => '',
        'type' => 2,
    ];
});

$factory->state(FuelTankOperation::class, 'income', function ($faker) {
    $contractor = Contractor::count() ? Contractor::inRandomOrder()->first() : factory(Contractor::class)->create();

    return [
        'contractor_id' => $contractor->id,
        'our_technic_id' => '',
        'type' => 1,
    ];
});

$factory->state(FuelTankOperation::class, 'manual', function ($faker) {
    return [
        'contractor_id' => '',
        'our_technic_id' => '',
        'type' => 3,
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Department;
use App\Models\Group;
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\TechAcc\OurTechnicTicket;

use Faker\Generator as Faker;

$factory->define(OurTechnicTicketReport::class, function (Faker $faker) {

    $rps = Group::with('users')->find([27, 13, 14])->pluck('users')->flatten();
    $rps_and_prorabs = Group::with('users')->find([27, 13, 19, 31, 14, 23])->pluck('users')->flatten();
    $warehouse_users = Department::with('users')->find([12, 13])->pluck('users')->flatten();

    $ticket = OurTechnicTicket::count() ? OurTechnicTicket::first() : factory(OurTechnicTicket::class)->create();

    return [
        'our_technic_ticket_id' => $ticket->id,
        'hours' => mt_rand(1, 24),
        'user_id' => $rps->random()->id,
        'comment' => $faker->text(150),
        'date' => \Carbon\Carbon::now(),
    ];
});

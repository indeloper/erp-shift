<?php

namespace Database\Factories\TechAcc;

use App\Models\Department;
use App\Models\Group;
use App\Models\TechAcc\OurTechnicTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

class OurTechnicTicketReportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $rps = Group::with('users')->find([27, 13, 14])->pluck('users')->flatten();
        $rps_and_prorabs = Group::with('users')->find([27, 13, 19, 31, 14, 23])->pluck('users')->flatten();
        $warehouse_users = Department::with('users')->find([12, 13])->pluck('users')->flatten();

        $ticket = OurTechnicTicket::count() ? OurTechnicTicket::first() : OurTechnicTicket::factory()->create();

        return [
            'our_technic_ticket_id' => $ticket->id,
            'hours' => mt_rand(1, 24),
            'user_id' => $rps->random()->id,
            'comment' => $this->faker->text(150),
            'date' => \Carbon\Carbon::now(),
        ];
    }
}

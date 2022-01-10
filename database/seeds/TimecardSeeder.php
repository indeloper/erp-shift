<?php

use App\Models\HumanResources\Timecard;
use App\Models\HumanResources\TimecardRecord;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimecardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $author = User::active()->inRandomOrder()->first();
        $worker = User::active()->inRandomOrder()->first();
        $another_worker = User::active()->inRandomOrder()->first();
        $timecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'author_id' => $author->id, 'month' => Carbon::now()->month]);
        $second_timecard = factory(Timecard::class)->create(['user_id' => $another_worker->id, 'author_id' => $author->id, 'month' => Carbon::now()->month]);

        foreach ([$timecard, $second_timecard] as $card) {
            for ($i = 0; $i < 10; $i++) {
                factory(TimecardRecord::class, 2)->state('working_hours')->create(['timecard_day_id' => $card->days()->inRandomOrder()->first()->id]);
            }
            for ($i = 0; $i < 4; $i++) {
                factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $card->days()->inRandomOrder()->first()->id]);
            }
            for ($i = 0; $i < 2; $i++) {
                $project_id = Project::inRandomOrder()->first()->id;
                factory(TimecardRecord::class, 3)->state('time_periods')->create(['project_id' => $project_id, 'timecard_day_id' => $card->days()->inRandomOrder()->first()->id]);
            }
            for ($i = 0; $i < 6; $i++) {
                $project_id = Project::inRandomOrder()->first()->id;
                factory(TimecardRecord::class, 1)->state('time_periods')->create(['project_id' => $project_id, 'timecard_day_id' => $card->days()->inRandomOrder()->first()->id]);
            }
        }
    }
}

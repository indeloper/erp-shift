<?php

namespace Tests\Feature\HumanResources;

use App\Models\HumanResources\{Brigade, TariffRates, Timecard, TimecardAddition, TimecardDay, TimecardRecord};
use App\Models\{Project, User};
use App\Services\HumanResources\TimecardService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TimecardApiTest extends TestCase
{
    /** @test */
    public function it_returns_well_structured_data_for_summary_timecard()
    {
        $timecard = factory(Timecard::class)->create();
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecard10 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 10,]);
        $timecard2 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 2,]);
        $timecard3 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 3,]);
        $timecard25 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 25,]);

        $time = factory(TimecardRecord::class, 2)->state('time_periods')->create(['timecard_day_id' => $timecard3->id]); //this is not used, but we need to have some extra data in database
        $hours = factory(TimecardRecord::class, 2)->state('working_hours')->create(['timecard_day_id' => $timecard10->id, 'tariff_id' => 3,  'amount' => 10]);
        $hours->push(factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecard25->id, 'tariff_id' => 5,  'amount' => 6]));
        $hours->push(factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecard2->id, 'tariff_id' => 5, 'amount' => 6]));
        $deals = factory(TimecardRecord::class, 3)->state('deal')->create(['timecard_day_id' => $timecard2->id, 'tariff_id' => 10, 'length' => 2, 'amount' => 4]);
        $deals->push(factory(TimecardRecord::class, 1)->state('deal')->create(['timecard_day_id' => $timecard3->id, 'tariff_id' => 11, 'length' => 5, 'amount' => 5]));
        $deals->push(factory(TimecardRecord::class, 1)->state('deal')->create(['timecard_day_id' => $timecard10->id, 'tariff_id' => 11, 'length' => 5, 'amount' => 5]));


        $data = $timecard->summarized_data;
//        dd($data->toArray());
        $this->assertEquals(5, $data->count());
        $this->assertEquals(20, $data[3]->sum);
        $this->assertEquals(50, $data[11]->sum);
//        [
//            [
//                'full_name' => 'Bill Gates',
//                'other_fields' => 'some_data',
//                'timecard' => [
//                    0 => [
//                        'name' => 'Сумма часов',
//                        'sum' => '120',
//                    ],
//                    1 => [
//                        'name' => 'Обычный час',
//                        'sum' => '60',
//                    ],
//                    2 => [
//                        'name' => 'Переработка',
//                        'sum' => '20',
//                    ],
//                    7 => [
//                        'name' => 'Простой',
//                        'sum' => '10',
//                    ],
//                    10 => [
//                        'name' => 'Погружение вдвоём вибро',
//                        'sum' => '30'
//                    ]
//                ]
//            ],
//            [
//                'full_name' => 'Steve Jobs',
//                'other_fields' => 'some_data',
//                'timecard' => [
//                    0 => [
//                        'name' => 'Сумма часов',
//                        'sum' => '100',
//                    ],
//                    //.....
//                ]
//            ],
//        ];
    }

    /** @test */
    public function it_can_return_detailed_info_about_user()
    {
        $this->withoutExceptionHandling();
        $timecard = factory(Timecard::class)->create();

        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecard10 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 10,]);
        $timecard2 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 2,]);
        $timecard3 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 3,]);
        $timecard5 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 5,]);
        $timecard28 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 28,]);

        $time = factory(TimecardRecord::class)->state('time_periods')->create(['timecard_day_id' => $timecard2->id, ]);
        $time = factory(TimecardRecord::class)->state('time_periods')->create(['timecard_day_id' => $timecard5->id, ]);
        $bonus = factory(TimecardAddition::class)->create(['type' => TimecardAddition::TYPES_ENG['bonus']]);
        $timecard->additions()->save($bonus);

        $hours = factory(TimecardRecord::class, 2)->state('working_hours')->create(['timecard_day_id' => $timecard3->id, 'tariff_id' => 3, 'amount' => 10]);
        $hours->push(factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecard5->id, 'tariff_id' => 5, 'amount' => 6]));
        $hours->push(factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecard10->id, 'tariff_id' => 5, 'amount' => 6]));

        $deals = factory(TimecardRecord::class, 3)->state('deal')->create(['timecard_day_id' => $timecard10->id, 'tariff_id' => 10, 'length' => 2, 'amount' => 4]);
        $deals->push(factory(TimecardRecord::class, 1)->state('deal')->create(['timecard_day_id' => $timecard10->id, 'tariff_id' => 11, 'length' => 5, 'amount' => 5]));
        $deals->push(factory(TimecardRecord::class, 1)->state('deal')->create(['timecard_day_id' => $timecard28->id, 'tariff_id' => 11, 'length' => 5, 'amount' => 5]));

        $data = $timecard->detailed_data;

        $payload = [
          'month' => "{$timecard->year}-{$timecard->month}",
          'user_id' => $timecard->user_id,
        ];

        $data = $this->actingAs(User::first())->post(route('human_resources.report.detailed_data'), $payload)->assertOk()->json('data');

        $this->assertCount(5, $data['detailed_data']);
        $this->assertCount(5, $data['detailed_data'][10]);
        $this->assertEquals(12, $data['summarized_data']['5']['sum']);
        $this->assertEquals(50, $data['summarized_data']['11']['sum']);
        $this->assertNotNull($data['bonuses']);
//        [
//            5 => [
//                [
//                    'type' => 1,
//                    'start' => '10:00',
//                    'end' => '12:00',
//                    'project' => [
//                        'name' => 'Google',
//                        'adress' => 'NY',
//                    ]
//                ],
//                [
//                    'type' => 2,
//                    'sum' => 100,
//                    'tariff_name' => 'Обычный час',
//                ],
//            ],
//            6 => [
//                [
//                    'type' => 1,
//                    'start' => '10:00',
//                    'end' => '12:00',
//                    'project' => [
//                        'name' => 'Google',
//                        'adress' => 'NY',
//                    ]
//                ],
//                [
//                    'type' => 3,
//                    'tariff_name' => 'Погружение',
//                    'length' => 12,
//                    'amount' => 24,
//                ],
//                [
//                    'type' => 1,
//                    'start' => '15:00',
//                    'end' => '17:00',
//                    'project' => [
//                        'name' => 'Yandex',
//                        'adress' => 'Moscow',
//                    ]
//                ],
//            ]
//        ];
    }

    /** @test */
    public function timecard_day_filter_scope_can_return_nothing(): void
    {
        // Scope will be work with two parameters - project and date. If we don't provide any data about project, it's should return nothing
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => TimecardTest::ABLE_TO_FILL_TIMECARD[array_rand(TimecardTest::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove all days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);

        // When user make post request with data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.timecard_day.get'), $data)->json();

        // Then user should have empty response
        $this->assertEmpty($response['data']['days']);
    }

    /** @test */
    public function timecard_day_filter_scope_can_return_nothing_if_project_dont_have_workers(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => TimecardTest::ABLE_TO_FILL_TIMECARD[array_rand(TimecardTest::ABLE_TO_FILL_TIMECARD)]]);
        // Given timecard
        $timecard = factory(Timecard::class)->create();
        // Remove all days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given project
        $project = factory(Project::class)->create();

        // When user make post request with data
        $data = ['project_id' => $project->id];
        $response = $this->actingAs($user)->post(route('human_resources.timecard_day.get'), $data)->json();

        // Then user should have empty response
        $this->assertEmpty($response['data']['days']);
    }

    /** @test */
    public function timecard_day_filter_scope_can_return_timecard_days_if_project_have_workers(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => TimecardTest::ABLE_TO_FILL_TIMECARD[array_rand(TimecardTest::ABLE_TO_FILL_TIMECARD)]]);
        // Given project
        $project = factory(Project::class)->create();
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        // Given timecards
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => now()->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => now()->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => now()->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => now()->day]);

        // When user make post request with data
        $data = ['project_id' => $project->id];
        $response = $this->actingAs($user)->post(route('human_resources.timecard_day.get'), $data)->json();

        // Then user should have something in response
        $this->assertCount(2, $response['data']['days']);
        $this->assertEquals(collect([$firstTimecardDay, $secondTimecardDay])->pluck('id'), collect($response['data']['days'])->pluck('id'));
    }

    /** @test */
    public function timecard_day_filter_scope_can_return_timecard_days_by_some_date_if_project_have_workers_and_timecards_on_this_date(): void
    {
        // Given user with permission
        $user = factory(User::class)->create(['group_id' => TimecardTest::ABLE_TO_FILL_TIMECARD[array_rand(TimecardTest::ABLE_TO_FILL_TIMECARD)]]);
        // Given project
        $project = factory(Project::class)->create();
        // Given workers for project
        $worker = factory(User::class)->create();
        $project->users()->save($worker);
        $brigade = factory(Brigade::class)->create();
        $brigadeWorker = factory(User::class)->create();
        $brigade->users()->save($brigadeWorker);
        $project->brigades()->save($brigade);
        $date = now()->subWeek();
        // Given timecards
        $firstTimecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'month' => $date->month]);
        $secondTimecard = factory(Timecard::class)->create(['user_id' => $brigadeWorker->id, 'month' => $date->month]);
        // Remove all days
        $firstTimecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days
        $firstTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $firstTimecard->id, 'day' => $date->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $date->day]);

        // When user make post request with data
        $data = ['project_id' => $project->id, 'date' => $date->format('d.m.Y')];
        $response = $this->actingAs($user)->post(route('human_resources.timecard_day.get'), $data)->json();

        // Then user should have something in response
        $this->assertCount(2, $response['data']['days']);
        $this->assertEquals(collect([$firstTimecardDay, $secondTimecardDay])->pluck('id'), collect($response['data']['days'])->pluck('id'));
    }

    /** @test */
    public function it_collect_data_for_project_on_day()
    {
        $author = User::inRandomOrder()->first();
        $worker = User::inRandomOrder()->first();
        $another_worker = User::inRandomOrder()->first();
        $project = Project::inRandomOrder()->first() ?? factory(Project::class)->create();
        $project->users()->sync([$worker->id, $another_worker->id]);
        $timecard = factory(Timecard::class)->create(['user_id' => $worker->id, 'author_id' => $author->id, 'month' => Carbon::now()->month]);
        $second_timecard = factory(Timecard::class)->create(['user_id' => $another_worker->id, 'author_id' => $author->id, 'month' => Carbon::now()->month]);

        foreach ([$timecard, $second_timecard] as $card) {
            factory(TimecardRecord::class, 3)->state('time_periods')->create(['timecard_day_id' => $card->days()->where('day', 5)->first()->id, 'project_id' => $project->id]);
            factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $card->days()->where('day', 5)->first()->id, 'tariff_id' => 1]);
            factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $card->days()->where('day', 5)->first()->id, 'tariff_id' => 3]);
            factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $card->days()->where('day', 5)->first()->id, 'tariff_id' => 5]);
            factory(TimecardRecord::class, 2)->state('deal')->create(['timecard_day_id' => $card->days()->where('day', 5)->first()->id, 'tariff_id' => 11]);
            factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $card->days()->where('day', 5)->first()->id, 'tariff_id' => 10]);
        }

        $data = (new TimecardService())->collectDailyTimecards(5, $project->id);

        $this->assertEquals(2, count($data));
        $this->assertEquals(7, count($data[$worker->id]));
    }

    /** @test */
    public function it_returns_well_structured_data_for_summary_timecard_for_custom_period()
    {
        $timecard = factory(Timecard::class)->create(['month' => '04']);
        $timecard02 = factory(Timecard::class)->create(['month' => '03']);

        // Remove previous days
        $timecard->days()->delete();
        $timecard02->days()->delete();
        // Given timecard day
        $timecard10 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 10,]);
        $timecard2 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 2,]);
        $timecard3 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 3,]);
        $timecard25 = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => 25,]);
        $timecard30 = factory(TimecardDay::class)->create(['timecard_id' => $timecard02->id, 'day' => 30,]);

        $time = factory(TimecardRecord::class, 2)->state('time_periods')->create(['timecard_day_id' => $timecard3->id]); //this is not used, but we need to have some extra data in database
        $hours = factory(TimecardRecord::class, 2)->state('working_hours')->create(['timecard_day_id' => $timecard10->id, 'tariff_id' => 3,  'amount' => 10]);
        $hours->push(factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecard25->id, 'tariff_id' => 5,  'amount' => 6]));
        $hours->push(factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecard2->id, 'tariff_id' => 5, 'amount' => 6]));
        $deals = factory(TimecardRecord::class, 3)->state('deal')->create(['timecard_day_id' => $timecard2->id, 'tariff_id' => 10, 'length' => 2, 'amount' => 4]);
        $deals->push(factory(TimecardRecord::class, 1)->state('deal')->create(['timecard_day_id' => $timecard3->id, 'tariff_id' => 11, 'length' => 5, 'amount' => 5]));
        $deals->push(factory(TimecardRecord::class, 1)->state('deal')->create(['timecard_day_id' => $timecard10->id, 'tariff_id' => 11, 'length' => 5, 'amount' => 5]));
        $deals->push(factory(TimecardRecord::class, 1)->state('deal')->create(['timecard_day_id' => $timecard30->id, 'tariff_id' => 11, 'length' => 5, 'amount' => 5]));


        $data = (new TimecardService())->collectSummaryForCustomPeriod(Carbon::parse('2020-03-01'), Carbon::parse('2020-04-26'));

        $this->assertEquals(5, $data['users'][$timecard->user_id]['timecards']->count());
        $this->assertEquals(20, $data['users'][$timecard->user_id]['timecards'][3]->sum);
        $this->assertEquals(50, $data['users'][$timecard->user_id]['timecards'][11]->sum);
//        [
//            [
//                'full_name' => 'Bill Gates',
//                'other_fields' => 'some_data',
//                'timecard' => [
//                    0 => [
//                        'name' => 'Сумма часов',
//                        'sum' => '120',
//                    ],
//                    1 => [
//                        'name' => 'Обычный час',
//                        'sum' => '60',
//                    ],
//                    2 => [
//                        'name' => 'Переработка',
//                        'sum' => '20',
//                    ],
//                    7 => [
//                        'name' => 'Простой',
//                        'sum' => '10',
//                    ],
//                    10 => [
//                        'name' => 'Погружение вдвоём вибро',
//                        'sum' => '30'
//                    ]
//                ]
//            ],
//            [
//                'full_name' => 'Steve Jobs',
//                'other_fields' => 'some_data',
//                'timecard' => [
//                    0 => [
//                        'name' => 'Сумма часов',
//                        'sum' => '100',
//                    ],
//                    //.....
//                ]
//            ],
//        ];
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_works_only_with_one_parameter(): void
    {
        // Scope will be work with three parameters - project, user and date. If we don't provide any data, it's should return nothing
        // Date - required parameter
        // Given user with permission
        $user = factory(User::class)->create();
        // Given timecard
        $timecard = factory(Timecard::class)->create();

        // When user make post request with data
        $data = [];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have empty response
        $this->assertEmpty($response);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_project_and_month(): void
    {
        // Scope will be work with three parameters - project, user and date. If we don't provide any data, it's should return nothing
        // Searching by project = search by timecard day records with same project id
        // Month by timecard
        // Given user
        $user = factory(User::class)->create();
        // Given timecardz
        $timecard = factory(Timecard::class)->create(['month' => now()->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => now()->subMonth()->month]);
        // Given project
        $project = factory(Project::class)->create();
        // Given timecard days for both timecards
        $timecardDay = $timecard->days()->inRandomOrder()->first();
        $secondTimecardDay = $secondTimecard->days()->inRandomOrder()->first();
        // Given time periods for this days
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'project_id' => $project->id,
            'timecard_day_id' => $timecardDay->id,
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'project_id' => $project->id,
            'timecard_day_id' => $secondTimecardDay->id,
        ]);
        $hour = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $timecardDay->id]);
        $hour1 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $timecardDay->id]);
        $secondHour = factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $secondTimecardDay->id]);
        $deal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $timecardDay->id]);
        $secondDeal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $secondTimecardDay->id]);

        // When user make post request with data
        $data = ['project_id' => $project->id, 'date' => now()->format('Y-m')];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals(collect([$hour, 0, $hour1, $deal])->pluck('tariff_id')->toArray(), array_keys($response['data']['users'][0]['timecard']));
        $this->assertEquals($timecard->user_id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_project_and_time_period(): void
    {
        // Scope will be work with three parameters - project, user and date. If we don't provide any data, it's should return nothing
        // Searching by project = search by timecard day records with same project id
        // Period by timecard days
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        Timecard::query()->delete();
        $now = now();
        $timecard = factory(Timecard::class)->create(['month' => $now->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => $now->month]);
        // Given project
        $project = factory(Project::class)->create();
        $timecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days for both timecards
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => $now->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $now->addDays(2)->day]);
        // Given time periods for this days
        $timePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'project_id' => $project->id,
            'timecard_day_id' => $timecardDay->id,
        ]);
        $secondTimePeriod = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['time periods'],
            'start' => '8-00',
            'project_id' => $project->id,
            'timecard_day_id' => $secondTimecardDay->id,
        ]);
        $hour = factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecardDay->id]);
        $secondHour = factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $secondTimecardDay->id]);
        $deal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $timecardDay->id]);
        $secondDeal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $secondTimecardDay->id]);

        // When user make post request with data
        $data = ['project_id' => $project->id, 'date' => "{$now->subDay()->format('Y-m-d')}|{$now->addDay()->format('Y-m-d')}"];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals(collect([$secondHour, 0, $secondDeal])->pluck('tariff_id')->toArray(), array_keys($response['data']['users'][0]['timecard']));
        $this->assertEquals($secondTimecard->user_id, $response['data']['users'][0]['id']);

        // PAYLOAD SCHEME
        /*
        [
            'data' => [
                'users' => [
                    [
                        'id' => user id,
                        'full_name' => user full name,
                        'timecard' => [ // all deals and tariffs with sum
                            'id' => // tariff id [
                                'name' => tariff name,
                                'sum' => amount in hours,
                            ], [...]
                        ]
                    ],[...]
                ]
            ]
        ]
        */
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_project_and_time_period_with_more_data(): void
    {
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        Timecard::query()->delete();
        $timecard = factory(Timecard::class)->create(['month' => now()->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => now()->month]);
        // Given projects
        $project = factory(Project::class)->create();
        // Given timecard days for all timecards
        // In 5, 8 and 25 day of month
        $startTimecardDay = $timecard->days()->where('day', 5)->first();
        $startSecondTimecardDay = $secondTimecard->days()->where('day', 5)->first();
        $middleTimecardDay = $timecard->days()->where('day', 8)->first();
        $middleSecondTimecardDay = $secondTimecard->days()->where('day', 8)->first();
        $endTimecardDay = $timecard->days()->where('day', 25)->first();
        $endSecondTimecardDay = $secondTimecard->days()->where('day', 25)->first();
        // Given working hours and deals for this days
        $hour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $deal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);

        // When user make post request with data
        // Let use period from 6.xx to 26.xx
        // all records from start days must disappear from result
        $start = now()->day(6)->format('Y-m-d');
        $end = now()->day(26)->format('Y-m-d');
        $data = ['project_id' => $project->id, 'date' => "{$start}|{$end}"];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $expectedResult = [
            'data' => [
                'users' => [
                    0 => [
                        'id' => $timecard->user_id,
                        'full_name' => $timecard->user->full_name,
                        'user_info' => "{$timecard->user->group_name} / {$timecard->user->company_name}",
                        'long_full_name' => $timecard->user->long_full_name,
                        'timecard' => [
                            1 => [
                                'name' => 'Обычный час',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ],
                            0 => [
                                'name' => 'Сумма часов',
                                'sum' => 20, // 20 because only sum of working hours counts
                            ],
                            8 => [
                                'name' => 'Погружение вибро',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ]
                        ]
                    ],
                    1 => [
                        'id' => $secondTimecard->user_id,
                        'full_name' => $secondTimecard->user->full_name,
                        'user_info' => "{$secondTimecard->user->group_name} / {$secondTimecard->user->company_name}",
                        'long_full_name' => $secondTimecard->user->long_full_name,
                        'timecard' => [
                            1 => [
                                'name' => 'Обычный час',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ],
                            0 => [
                                'name' => 'Сумма часов',
                                'sum' => 20, // 20 because only sum of working hours counts
                            ],
                            8 => [
                                'name' => 'Погружение вибро',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(2, $response['data']['users']);
        $this->assertEquals($expectedResult, $response);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_project_and_time_period_with_more_data_second_time(): void
    {
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        Timecard::query()->delete();
        $timecard = factory(Timecard::class)->create(['month' => now()->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => now()->month]);
        // Given projects
        $project = factory(Project::class)->create();
        $secondProject = factory(Project::class)->create();
        // Given timecard days for all timecards
        // In 5, 8 and 25 day of month
        $startTimecardDay = $timecard->days()->where('day', 5)->first();
        $startSecondTimecardDay = $secondTimecard->days()->where('day', 5)->first();
        $middleTimecardDay = $timecard->days()->where('day', 8)->first();
        $middleSecondTimecardDay = $secondTimecard->days()->where('day', 8)->first();
        $endTimecardDay = $timecard->days()->where('day', 25)->first();
        $endSecondTimecardDay = $secondTimecard->days()->where('day', 25)->first();
        // Given working hours and deals for this days
        $hour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $secondProject->id, 'timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $secondProject->id, 'timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $secondProject->id, 'timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $secondProject->id, 'timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $deal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);

        // When user make post request with data
        // Let use period from 6.xx to 26.xx
        // all records from start days must disappear from result
        $start = now()->day(6)->format('Y-m-d');
        $end = now()->day(26)->format('Y-m-d');
        $data = ['project_id' => $project->id, 'date' => "{$start}|{$end}"];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $expectedResult = [
            'data' => [
                'users' => [
                    0 => [
                        'id' => $timecard->user_id,
                        'full_name' => $timecard->user->full_name,
                        'user_info' => "{$timecard->user->group_name} / {$timecard->user->company_name}",
                        'long_full_name' => $timecard->user->long_full_name,
                        'timecard' => [
                            1 => [
                                'name' => 'Обычный час',
                                'sum' => 10, // 10 because records from day 5 and secondProject was filtered out
                            ],
                            0 => [
                                'name' => 'Сумма часов',
                                'sum' => 10, // 20 because only sum of working hours counts
                            ],
                            8 => [
                                'name' => 'Погружение вибро',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals($expectedResult, $response);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_month(): void
    {
        // Scope will be work with three parameters - project, user and date. If we don't provide any data, it's should return nothing
        // Given user
        $user = factory(User::class)->create();
        Timecard::query()->delete();
        // Given timecard
        $timecard = factory(Timecard::class)->create(['month' => now()->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => now()->subMonth()->month]);
        // Given timecard days for both timecards
        $timecardDay = $timecard->days()->inRandomOrder()->first();
        $secondTimecardDay = $secondTimecard->days()->inRandomOrder()->first();

        // When user make post request with data
        $data = ['date' => now()->format('Y-m')];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals($timecard->user_id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_time_period(): void
    {
        // Scope will be work with three parameters - project, user and date. If we don't provide any data, it's should return nothing
        // Period by timecard days
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        Timecard::query()->delete();
        $now = now();
        $timecard = factory(Timecard::class)->create(['month' => $now->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => $now->month]);
        // Remove timecard days
        $timecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days for both timecards
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => $now->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $now->addDays(2)->day]);
        // Given hours and deals
        $hour = factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecardDay->id]);
        $secondHour = factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $secondTimecardDay->id]);
        $deal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $timecardDay->id]);
        $secondDeal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $secondTimecardDay->id]);

        // When user make post request with data
        $data = ['date' => "{$now->subDay()->format('Y-m-d')}|{$now->addDay()->format('Y-m-d')}"];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals(collect([$secondHour, 0, $secondDeal])->pluck('tariff_id')->toArray(), array_keys($response['data']['users'][0]['timecard']));
        $this->assertEquals($secondTimecard->user_id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_time_period_with_more_data(): void
    {
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        Timecard::query()->delete();
        $timecard = factory(Timecard::class)->create(['month' => now()->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => now()->month]);
        // Given projects
        $project = factory(Project::class)->create();
        // Given timecard days for all timecards
        // In 5, 8 and 25 day of month
        $startTimecardDay = $timecard->days()->where('day', 5)->first();
        $startSecondTimecardDay = $secondTimecard->days()->where('day', 5)->first();
        $middleTimecardDay = $timecard->days()->where('day', 8)->first();
        $middleSecondTimecardDay = $secondTimecard->days()->where('day', 8)->first();
        $endTimecardDay = $timecard->days()->where('day', 25)->first();
        $endSecondTimecardDay = $secondTimecard->days()->where('day', 25)->first();
        // Given working hours and deals for this days
        $hour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $deal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);

        // When user make post request with data
        // Let use period from 6.xx to 26.xx
        // all records from start days must disappear from result
        $start = now()->day(6)->format('Y-m-d');
        $end = now()->day(26)->format('Y-m-d');
        $data = ['date' => "{$start}|{$end}"];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $expectedResult = [
            'data' => [
                'users' => [
                    0 => [
                        'id' => $timecard->user_id,
                        'full_name' => $timecard->user->full_name,
                        'user_info' => "{$timecard->user->group_name} / {$timecard->user->company_name}",
                        'long_full_name' => $timecard->user->long_full_name,
                        'timecard' => [
                            1 => [
                                'name' => 'Обычный час',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ],
                            0 => [
                                'name' => 'Сумма часов',
                                'sum' => 20, // 20 because only sum of working hours counts
                            ],
                            8 => [
                                'name' => 'Погружение вибро',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ]
                        ]
                    ],
                    1 => [
                        'id' => $secondTimecard->user_id,
                        'full_name' => $secondTimecard->user->full_name,
                        'user_info' => "{$secondTimecard->user->group_name} / {$secondTimecard->user->company_name}",
                        'long_full_name' => $secondTimecard->user->long_full_name,
                        'timecard' => [
                            1 => [
                                'name' => 'Обычный час',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ],
                            0 => [
                                'name' => 'Сумма часов',
                                'sum' => 20, // 20 because only sum of working hours counts
                            ],
                            8 => [
                                'name' => 'Погружение вибро',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(2, $response['data']['users']);
        $this->assertEquals($expectedResult, $response);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_user_and_month(): void
    {
        // Scope will be work with three parameters - project, user and date. If we don't provide any data, it's should return nothing
        // Given user
        $user = factory(User::class)->create();
        Timecard::query()->delete();
        // Given timecard
        $timecard = factory(Timecard::class)->create(['month' => now()->month, 'user_id' => $user->id]);
        $secondTimecard = factory(Timecard::class)->create(['month' => now()->month]);
        // Given timecard days for both timecards
        $timecardDay = $timecard->days()->inRandomOrder()->first();
        $secondTimecardDay = $secondTimecard->days()->inRandomOrder()->first();

        // When user make post request with data
        $data = ['user_id' => $timecard->user_id, 'date' => now()->format('Y-m')];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals($user->id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_user_and_time_period(): void
    {
        // Scope will be work with three parameters - project, user and date. If we don't provide any data, it's should return nothing
        // Period by timecard days
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        Timecard::query()->delete();
        $now = now();
        $timecard = factory(Timecard::class)->create(['month' => $now->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => $now->month, 'user_id' => $user->id]);
        // Remove timecard days
        $timecard->days()->delete();
        $secondTimecard->days()->delete();
        // Given timecard days for both timecards
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id, 'day' => $now->day]);
        $secondTimecardDay = factory(TimecardDay::class)->create(['timecard_id' => $secondTimecard->id, 'day' => $now->addDays(2)->day]);
        // Given hours and deals
        $hour = factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $timecardDay->id]);
        $secondHour = factory(TimecardRecord::class)->state('working_hours')->create(['timecard_day_id' => $secondTimecardDay->id]);
        $deal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $timecardDay->id]);
        $secondDeal = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $secondTimecardDay->id]);

        // When user make post request with data
        $data = ['user_id' => $user->id, 'date' => "{$now->subDay()->format('Y-m-d')}|{$now->addDay()->format('Y-m-d')}"];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals(collect([$secondHour, 0, $secondDeal])->pluck('tariff_id')->toArray(), array_keys($response['data']['users'][0]['timecard']));
        $this->assertEquals($secondTimecard->user_id, $response['data']['users'][0]['id']);
    }

    /** @test */
    public function timecard_report_getter_for_summary_report_can_find_timecards_for_user_and_time_period_with_more_data(): void
    {
        // Given user
        $user = factory(User::class)->create();
        // Given timecard
        Timecard::query()->delete();
        $timecard = factory(Timecard::class)->create(['month' => now()->month]);
        $secondTimecard = factory(Timecard::class)->create(['month' => now()->month]);
        // Given projects
        $project = factory(Project::class)->create();
        // Given timecard days for all timecards
        // In 5, 8 and 25 day of month
        $startTimecardDay = $timecard->days()->where('day', 5)->first();
        $startSecondTimecardDay = $secondTimecard->days()->where('day', 5)->first();
        $middleTimecardDay = $timecard->days()->where('day', 8)->first();
        $middleSecondTimecardDay = $secondTimecard->days()->where('day', 8)->first();
        $endTimecardDay = $timecard->days()->where('day', 25)->first();
        $endSecondTimecardDay = $secondTimecard->days()->where('day', 25)->first();
        // Given working hours and deals for this days
        $hour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour5 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour8 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $hour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $secondHour25 = factory(TimecardRecord::class)->state('working_hours')->create(['project_id' => $project->id, 'timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 1]);
        $deal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal5 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $startSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal8 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $middleSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $deal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);
        $secondDeal25 = factory(TimecardRecord::class)->state('deal')->create(['timecard_day_id' => $endSecondTimecardDay->id, 'amount' => 10, 'tariff_id' => 8]);

        // When user make post request with data
        // Let use period from 6.xx to 26.xx
        // all records from start days must disappear from result
        $start = now()->day(6)->format('Y-m-d');
        $end = now()->day(26)->format('Y-m-d');
        $data = ['user_id' => $timecard->user_id, 'date' => "{$start}|{$end}"];
        $response = $this->actingAs($user)->post(route('human_resources.timecard.get_summary_report'), $data)->json();

        // Then user should have something in response
        $expectedResult = [
            'data' => [
                'users' => [
                    0 => [
                        'id' => $timecard->user_id,
                        'full_name' => $timecard->user->full_name,
                        'user_info' => "{$timecard->user->group_name} / {$timecard->user->company_name}",
                        'long_full_name' => $timecard->user->long_full_name,
                        'timecard' => [
                            1 => [
                                'name' => 'Обычный час',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ],
                            0 => [
                                'name' => 'Сумма часов',
                                'sum' => 20, // 20 because only sum of working hours counts
                            ],
                            8 => [
                                'name' => 'Погружение вибро',
                                'sum' => 20, // 20 because records from day 5 was filtered out
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $this->assertNotEmpty($response['data']['users']);
        $this->assertCount(1, $response['data']['users']);
        $this->assertEquals($expectedResult, $response);
    }

    /** @test */
    public function group_deals_destroy_method_works()
    {
        $user = User::find(1);
        // Given timecard
        Timecard::query()->delete();
        $now = now();
        $timecard = factory(Timecard::class)->create(['month' => $now->month]);
        $deals = factory(TimecardRecord::class, 4)->state('deal')->create(['timecard_day_id' => $timecard->days()->first()->id]);

        $this->actingAs($user);
        $payload = [
            'timecard_id' => $timecard->id,
            'tariff_id' => $deals->first()->tariff_id,
            'length' => $deals->first()->length,
        ];
        $response = $this->delete(route('human_resources.timecard.destroy_deals_group'), $payload);

        $response->assertOk();
        $this->assertNull($timecard->deals()->where('timecard_records.id', $deals->first()->id)->first());
    }

    /** @test */
    public function group_day_deals_destroy_method_works()
    {
        $user = User::find(1);
        $project = Project::first();
        $project->users()->attach($user->id);
        // Given timecard
        $timecard = factory(Timecard::class)->create(['user_id' => $user->id]);
        // Remove previous days
        $timecard->days()->delete();
        // Given timecard day
        $timecardDay = factory(TimecardDay::class)->create(['timecard_id' => $timecard->id]);
        // Given deal
        $deal = TariffRates::whereType(2)->inRandomOrder()->first();
        // Given working deal record
        $dealRecord = factory(TimecardRecord::class)->create([
            'type' => TimecardRecord::TYPES_ENG['deals'],
            'tariff_id' => $deal->id,
            'length' => '14.5',
            'amount' => '8',
            'timecard_day_id' => $timecardDay->id,
        ]);
        $this->withoutExceptionHandling();
        $this->actingAs($user);
        $payload = [
            'project_id' => $project->id,
            'day' => "$timecardDay->day.$timecard->month.$timecard->year",
            'tariff_id' => $dealRecord->tariff_id,
            'length' => $dealRecord->length,
        ];

        $response = $this->delete(route('human_resources.timecard_day.destroy_day_deals_group'), $payload);

        $response->assertOk();
        $this->assertNull($timecard->deals()->where('timecard_records.id', $dealRecord->id)->first());
    }
}

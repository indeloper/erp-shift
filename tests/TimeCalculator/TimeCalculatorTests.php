<?php

namespace Tests\TimeCalculator;

use App\Traits\TimeCalculator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimeCalculatorTests extends TestCase
{
    public function test_add_0_hours_to_monday()
    {
        // curr_time = 17:20
        // add zero hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(0)->hour(17)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(0, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_1_hours_to_monday()
    {
        // curr_time = 17:20
        // add one hour
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(0)->hour(18)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(1, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    // tuesday
    public function test_add_2_hours_to_monday()
    {
        // curr_time = 17:20
        // add two hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->hour(8)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(2, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_3_hours_to_monday()
    {
        // curr_time = 17:20
        // add three hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->hour(9)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(3, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_5_hours_to_monday()
    {
        // curr_time = 17:20
        // add five hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->hour(11)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(5, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_8_hours_to_monday()
    {
        // curr_time = 17:20
        // add eight hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->hour(14)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(8, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_10_hours_to_monday()
    {
        // curr_time = 17:20
        // add ten hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->hour(16)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(10, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_12_hours_to_monday()
    {
        // curr_time = 17:20
        // add twelve hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->hour(18)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(12, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    // friday
    public function test_add_14_hours_to_monday()
    {
        // curr_time = 17:20
        // add 14 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(2)->hour(9)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(14, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_15_hours_to_monday()
    {
        // curr_time = 17:20
        // add 15 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(2)->hour(10)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(15, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_16_hours_to_monday()
    {
        // curr_time = 17:20
        // add 16 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(2)->hour(11)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(16, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_17_hours_to_monday()
    {
        // curr_time = 17:20
        // add 17 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(2)->hour(12)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(17, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_18_hours_to_monday()
    {
        // curr_time = 17:20
        // add 18 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(2)->hour(13)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(18, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_19_hours_to_monday()
    {
        // curr_time = 17:20
        // add 19 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(2)->hour(14)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(19, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_23_hours_to_monday()
    {
        // curr_time = 17:20
        // add 23 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(2)->hour(18)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(23, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    // thursday
    public function test_add_24_hours_to_monday()
    {
        // curr_time = 17:20
        // add 24 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(3)->hour(8)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(24, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_30_hours_to_monday()
    {
        // curr_time = 17:20
        // add 30 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(3)->hour(14)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(30, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_34_hours_to_monday()
    {
        // curr_time = 17:20
        // add 34 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(3)->hour(18)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(34, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    // friday
    public function test_add_35_hours_to_monday()
    {
        // curr_time = 17:20
        // add 35 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(4)->hour(8)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(35, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_42_hours_to_monday()
    {
        // curr_time = 17:20
        // add 42 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(4)->hour(15)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(42, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_45_hours_to_monday()
    {
        // curr_time = 17:20
        // add 45 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(4)->hour(18)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(45, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    // not saturday, monday
    public function test_add_46_hours_to_monday()
    {
        // curr_time = 17:20
        // add 46 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(0)->addWeek()->hour(8)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(46, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_48_hours_to_monday()
    {
        // curr_time = 17:20
        // add 48 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(0)->addWeek()->hour(10)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(48, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_50_hours_to_monday()
    {
        // curr_time = 17:20
        // add 50 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(0)->addWeek()->hour(12)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(50, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_60_hours_to_monday()
    {
        // curr_time = 17:20
        // add 60 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->addWeek()->hour(11)->minute(20)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(60, now()->weekday(0)->hour(17)->minute(20));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    // start from other time
    public function test_add_50_hours_to_monday_one_more_time()
    {
        // curr_time = 15:00
        // add 50 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(0)->addWeek()->hour(10)->minute(00)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(50, now()->weekday(0)->hour(15)->minute(00));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_48_hours_to_monday_one_more_time()
    {
        // curr_time = 15:00
        // add 48 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(4)->hour(19)->minute(00)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(48, now()->weekday(0)->hour(15)->minute(00));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_46_hours_to_monday_one_more_time()
    {
        // curr_time = 15:00
        // add 46 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(4)->hour(17)->minute(00)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(46, now()->weekday(0)->hour(15)->minute(00));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }

    public function test_add_60_hours_to_monday_one_more_time()
    {
        // curr_time = 15:00
        // add 60 hours
        $traitTestClass = new class { use TimeCalculator; };
        $expectations = now()->weekDay(1)->addWeek()->hour(9)->minute(00)->second(0)->micro(0);
        $reality = $traitTestClass->addHours(60, now()->weekday(0)->hour(15)->minute(00));

        $this->assertEquals($expectations, $reality);
        dump($expectations, $reality);
    }
}

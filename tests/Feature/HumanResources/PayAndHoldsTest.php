<?php

namespace Tests\Feature;

use App\Models\HumanResources\PayAndHold;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayAndHoldsTest extends TestCase
{

    /** @test */
    public function it_can_get_holds()
    {
        $count_of_holds = 4;
        factory(PayAndHold::class, $count_of_holds)->create(['type' => 2]);
        factory(PayAndHold::class, 6)->create(['type' => 1]);

        $this->assertEquals($count_of_holds, PayAndHold::holds()->count());
    }
}

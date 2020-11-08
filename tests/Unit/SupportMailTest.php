<?php

namespace Tests\Unit;

use App\Models\SupportMail;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupportMailTest extends TestCase
{
    /** @test */
    public function main_scope_order_support_mails_in_right_way()
    {
        // Given user
        $user = factory(User::class)->create();
        $this->actingAs($user);
        // Given support mails
        $supportMail1 = factory(SupportMail::class)->create(['status' => 'new']);
        $supportMail2 = factory(SupportMail::class)->create(['status' => 'in_work']);
        $supportMail3 = factory(SupportMail::class)->create(['status' => 'matching']);
        $supportMail4 = factory(SupportMail::class)->create(['status' => 'resolved']);
        $supportMail5 = factory(SupportMail::class)->create(['status' => 'accept']);
        $supportMail6 = factory(SupportMail::class)->create(['status' => 'decline']);

        // When we use basic scope
        $result = SupportMail::basic(request())->get();

        // Then collection should be ordered
        // First - active ordered by id desc, Second - nonactive
        $this->assertEquals([$supportMail5->id, $supportMail3->id, $supportMail2->id, $supportMail1->id, $supportMail6->id, $supportMail4->id], $result->pluck('id')->toArray());
    }

    /** @test */
    public function main_scope_work_with_request()
    {
        // Given user
        $user = factory(User::class)->create();
        $this->actingAs($user);
        // Given support mails
        $supportMail1 = factory(SupportMail::class)->create(['status' => 'new']);
        $supportMail2 = factory(SupportMail::class)->create(['status' => 'in_work']);
        $supportMail3 = factory(SupportMail::class)->create(['status' => 'matching']);
        $supportMail4 = factory(SupportMail::class)->create(['status' => 'resolved']);
        $supportMail5 = factory(SupportMail::class)->create(['status' => 'accept']);
        $supportMail6 = factory(SupportMail::class)->create(['status' => 'decline']);
        // Given request
        $request = Request::create('', 'GET', ['search' => $supportMail1->title]);

        // When we use basic scope
        $result = SupportMail::basic($request)->get();

        // Then collection should be ordered
        $this->assertEquals([$supportMail1->id], $result->pluck('id')->toArray());
    }
}

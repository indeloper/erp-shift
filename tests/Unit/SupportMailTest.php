<?php

namespace Tests\Unit;

use App\Models\SupportMail;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class SupportMailTest extends TestCase
{
    /** @test */
    public function main_scope_order_support_mails_in_right_way(): void
    {
        // Given user
        $user = User::factory()->create();
        $this->actingAs($user);
        // Given support mails
        $supportMail1 = SupportMail::factory()->create(['status' => 'new']);
        $supportMail2 = SupportMail::factory()->create(['status' => 'in_work']);
        $supportMail3 = SupportMail::factory()->create(['status' => 'matching']);
        $supportMail4 = SupportMail::factory()->create(['status' => 'resolved']);
        $supportMail5 = SupportMail::factory()->create(['status' => 'accept']);
        $supportMail6 = SupportMail::factory()->create(['status' => 'decline']);

        // When we use basic scope
        $result = SupportMail::basic(request())->get();

        // Then collection should be ordered
        // First - active ordered by id desc, Second - nonactive
        $this->assertEquals([$supportMail5->id, $supportMail3->id, $supportMail2->id, $supportMail1->id, $supportMail6->id, $supportMail4->id], $result->pluck('id')->toArray());
    }

    /** @test */
    public function main_scope_work_with_request(): void
    {
        // Given user
        $user = User::factory()->create();
        $this->actingAs($user);
        // Given support mails
        $supportMail1 = SupportMail::factory()->create(['status' => 'new']);
        $supportMail2 = SupportMail::factory()->create(['status' => 'in_work']);
        $supportMail3 = SupportMail::factory()->create(['status' => 'matching']);
        $supportMail4 = SupportMail::factory()->create(['status' => 'resolved']);
        $supportMail5 = SupportMail::factory()->create(['status' => 'accept']);
        $supportMail6 = SupportMail::factory()->create(['status' => 'decline']);
        // Given request
        $request = Request::create('', 'GET', ['search' => $supportMail1->title]);

        // When we use basic scope
        $result = SupportMail::basic($request)->get();

        // Then collection should be ordered
        $this->assertEquals([$supportMail1->id], $result->pluck('id')->toArray());
    }
}

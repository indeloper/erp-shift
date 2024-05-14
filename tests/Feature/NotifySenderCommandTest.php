<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotifySenderCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected $user1;

    protected $user2;

    protected $user3;

    public function setUp(): void
    {
        parent::setUp();

        $this->user1 = factory(User::class)->create(['chat_id' => 'anything']);
        $this->user2 = factory(User::class)->create(['chat_id' => 'second anything']);
        $this->user3 = factory(User::class)->create();
    }

    /** @test */
    public function withTelegramChatId_scope_must_return_users_who_have_telegram_chat_id()
    {
        // Given three users, two have chat_id
        $user1 = $this->user1;
        $user2 = $this->user2;
        $user3 = $this->user3;

        // When we use withTelegramId() scope
        $result = User::withTelegramChatId();
        // And get last two users
        $result->orderBy('id', 'desc')->take(2);
        // Then we should have users only with chat_id
        $this->assertEquals($result->pluck('chat_id')->toArray(), [$user2->chat_id, $user1->chat_id]);
    }

    /** @test */
    public function withoutTelegramChatId_scope_must_return_users_who_dont_have_telegram_chat_id()
    {
        // Given three users, two have chat_id
        $user1 = $this->user1;
        $user2 = $this->user2;
        $user3 = $this->user3;

        // When we use withoutTelegramId() scope
        $result = User::withoutTelegramChatId();
        // And get last two users
        $result = $result->get()->last();
        // Then we should have users only with chat_id
        $this->assertEquals($result->id, $user3->id);
    }

    /** @test */
    public function if_we_run_command_without_parameters_we_must_have_some_output_other_way()
    {
        // When we call notify:send command
        $this->artisan('notify:send')
            // And answer question
            ->expectsQuestion('Looks like you dont send any text here. Do you want to continue?', 'Yes')
            // Then we should have some standard output
            ->expectsOutput("Send notification with text: 'Стандартное сообщение', in system only for users without telegram");
    }
}

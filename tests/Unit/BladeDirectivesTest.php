<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BladeDirectivesTest extends TestCase
{
    /** @test */
    public function user_directive_returns_hyperlink(): void
    {
        // Given user
        $user = User::factory()->create();

        // When we use @user() blade directive
        $result = Blade::compileString("@user({$user->id})");

        // Then $result should be like this
        $this->assertEquals($result,
            '<a href='.route('users::card', $user->id).' class="activity-content__link">'.
            $user->long_full_name.'</a>'
        );
    }

    /** @test */
    public function user_directive_returns_404_if_receive_nonexistent_user(): void
    {
        // Then exception should be thrown
        $this->expectException(ModelNotFoundException::class);

        // When we use @user() blade directive with random number
        Blade::compileString('@user(-179)');
    }
}

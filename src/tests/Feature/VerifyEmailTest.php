<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;
    // 会員登録後、認証メールが送信される
    public function test_after_register_send_email()
    {
        Notification::fake();

        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);

        $response->assertRedirect('/email/verify');

        Notification::assertSentTo(
            User::where('email', 'test@example.com')->first(),
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }
}

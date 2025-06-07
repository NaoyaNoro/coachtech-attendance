<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required_for_login()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        try {
            $this->post('/admin/login', [
                '_token' => csrf_token(),
                'email' => '',
                'password' => 'password123',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $emailError = $e->errors()['email'][0];
            $this->assertEquals('メールアドレスを入力してください', $emailError);
            throw $e;
        }
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required_for_login()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        try {
            $this->post('/admin/login', [
                '_token' => csrf_token(),
                'email' => 'test@example.com',
                'password' => '',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $passwordError = $e->errors()['password'][0];
            $this->assertEquals('パスワードを入力してください', $passwordError);
            throw $e;
        }
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_no_user_is_for_login()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        Admin::factory()->create([
            'email' => 'correct@example.com',
            'password' => bcrypt('correct_password'),
        ]);

        try {
            $this->post('/admin/login', [
                '_token' => csrf_token(),
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $userError = $e->errors()['email'][0];
            $this->assertEquals('ログイン情報が登録されていません', $userError);
            throw $e;
        }
    }
}

<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Carbon;


class VerifyEmailTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */

    use DatabaseMigrations;

    public function testVerifyEmailTest()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'テストユーザー')
                ->type('email', 'test@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->press('登録する')
                ->assertPathIs('/email/verify')
                ->assertSee('認証はこちらから')

                ->click('@verify-link')
                ->pause(1000)

                ->assertUrlIs(env('MAILHOG_URL'));
        });
    }


    public function testEmailVerificationRedirectsToAttendance()
    {
        Carbon::setTestNow(Carbon::now());

        $this->browse(function (Browser $browser) {
            // 1. 登録
            $browser->visit('/register')
                ->type('name', 'テストユーザー')
                ->type('email', 'test@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->press('登録する')
                ->assertPathIs('/email/verify');

            // 2. 最新のメールからリンク取得
            $mail = json_decode(file_get_contents('http://host.docker.internal:8025/api/v2/messages'), true);
            $body = $mail['items'][0]['Content']['Body'];
            preg_match('/http:\/\/localhost:.*\/email\/verify\/\d+\/[^"]+/', $body, $matches);
            $verifyUrl = $matches[0] ?? null;

            // 3. 認証アクセス
            $user = User::where('email', 'test@example.com')->first();
            $browser->loginAs($user)
                ->visit($verifyUrl)
                ->waitForLocation('/')
                ->assertPathIs('/')
                ->assertSee('出勤');
        });
    }
}

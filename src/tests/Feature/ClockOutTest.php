<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    // 退勤ボタンが正しく機能する
    public function test_user_can_clock_out()
    {
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->actingAs($user)->post('/clock_out');

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('退勤済');
    }

    // 退勤時刻が管理画面で確認できる
    public function test_attendance_list_after_clock_out()
    {
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post('/clock_out');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(Carbon::now()->format('Y/m'));
        $html = $response->getContent();
        $expected = Carbon::now()->format('H:i');
        $this->assertEquals(2, substr_count($html, $expected));
    }
}

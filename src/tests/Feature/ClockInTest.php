<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    // 出勤ボタンが正しく機能する
    public function test_user_can_clock_in()
    {
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'before_working',
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/clock_in');

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('出勤中');
    }

    // 出勤は一日一回のみできる
    public function test_user_can_clock_in_once_per_day()
    {
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'after_working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    // 出勤時刻が管理画面で確認できる
    public function test_attendance_list_after_clock_in(){
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'before_working',
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post('/clock_in');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(Carbon::now()->format('Y/m'));
        $response->assertSee(Carbon::now()->format('H:i'));
    }
}

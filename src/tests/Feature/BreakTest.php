<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    // 休憩ボタンが正しく機能する
    public function test_user_can_break_start()
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
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->post('/break_start');

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('休憩中');
    }

    // 休憩は一日に何回でもできる
    public function test_user_can_break_start_several_times(){
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->post('/break_start');
        $response = $this->actingAs($user)->post('/break_end');

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    // 休憩戻ボタンが正しく機能する
    public function test_user_can_break_end()
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

        $response = $this->actingAs($user)->post('/break_start');
        $response = $this->actingAs($user)->get('/');
        $response->assertSee('休憩戻');

        $response = $this->actingAs($user)->post('/break_end');
        $response = $this->actingAs($user)->get('/');
        $response->assertSee('出勤中');
    }

    // 休憩戻は一日に何回でもできる
    public function test_user_can_break_end_several_times() {
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
        ]);

        $this->actingAs($user)->post('/break_start');
        $this->actingAs($user)->post('/break_end');
        $this->actingAs($user)->post('/break_start');

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('休憩戻');
    }

    // 勤怠一覧画面に休憩時刻が正確に記録されている
    public function test_attendance_list_break_time() {
        $user = User::factory()->create()->first();

        Status::create([
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        Carbon::setTestNow(Carbon::parse('2025-05-25 12:00:00'));
        $this->actingAs($user)->post('/break_start');

        Carbon::setTestNow(Carbon::parse('2025-05-25 12:30:00'));
        $this->actingAs($user)->post('/break_end');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('00:30');
    }

}

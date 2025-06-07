<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class DetailTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_detail_attendance_name()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSeeInOrder([
            "名前","{$user->name}"
        ]);
    }

    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function test_detail_attendance_day()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSeeInOrder([
            "日付",
            "2025年",
            "5月25日"
        ]);
    }

    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_detail_attendance_clock_in_out()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSeeInOrder([
            "出勤・退勤",
            "09:00",
            "17:00"
        ]);
    }

    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_detail_attendance_break_time()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        BreakTime::create([
            'attendance_id'=>$attendance->id,
            'break_start'=> Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSeeInOrder([
            "休憩1",
            "12:00",
            "13:00"
        ]);
    }
}

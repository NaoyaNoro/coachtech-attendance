<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class AdminListTest extends TestCase
{
    use RefreshDatabase;

    // その日になされた全ユーザーの勤怠情報が正確に確認できる + 遷移した際に現在の日付が表示される
    public function test_today_all_attendance()
    {
        $user1 = User::factory()->create()->first();
        Attendance::create([
            'user_id' => $user1->id,
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(17, 0, 0),
        ]);

        $user2 = User::factory()->create()->first();
        $attendnace2=Attendance::create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::now()->setTime(10, 0, 0),
            'clock_out' => Carbon::now()->setTime(18, 0, 0),
        ]);

        BreakTime::create([
            'attendance_id'=> $attendnace2->id,
            'break_start'=> Carbon::now()->setTime(12, 0, 0),
            'break_end'=> Carbon::now()->setTime(13, 0, 0),
        ]);

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/list");

        $response->assertSee(Carbon::now()->format('Y/m/d'));

        $response->assertSeeInOrder([
            "{$user1->name}",
            "09:00",
            "17:00",
            "00:00",
            "08:00",
            "詳細",
        ]);

        $response->assertSeeInOrder([
            "{$user2->name}",
            "10:00",
            "18:00",
            "01:00",
            "07:00",
            "詳細",
        ]);
    }

    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_yesterday_all_attendance()
    {
        $user1 = User::factory()->create()->first();
        Attendance::create([
            'user_id' => $user1->id,
            'clock_in' => Carbon::now()->subDay()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->subDay()->setTime(17, 0, 0),
        ]);

        $user2 = User::factory()->create()->first();
        $attendnace2 = Attendance::create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::now()->subDay()->setTime(10, 0, 0),
            'clock_out' => Carbon::now()->subDay()->setTime(18, 0, 0),
        ]);

        BreakTime::create([
            'attendance_id' => $attendnace2->id,
            'break_start' => Carbon::now()->subDay()->setTime(12, 0, 0),
            'break_end' => Carbon::now()->subDay()->setTime(13, 0, 0),
        ]);

        $admin = Admin::factory()->create()->first();

        $day = Carbon::now()->subDay()->format('Y-m-d');
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/list?day={$day}");

        $response->assertSee(Carbon::now()->subDay()->format('Y/m/d'));

        $response->assertSeeInOrder([
            "{$user1->name}",
            "09:00",
            "17:00",
            "00:00",
            "08:00",
            "詳細",
        ]);

        $response->assertSeeInOrder([
            "{$user2->name}",
            "10:00",
            "18:00",
            "01:00",
            "07:00",
            "詳細",
        ]);
    }

    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_tomorrow_all_attendance()
    {
        $user1 = User::factory()->create()->first();
        Attendance::create([
            'user_id' => $user1->id,
            'clock_in' => Carbon::now()->addDay()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->addDay()->setTime(17, 0, 0),
        ]);

        $user2 = User::factory()->create()->first();
        $attendnace2 = Attendance::create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::now()->addDay()->setTime(10, 0, 0),
            'clock_out' => Carbon::now()->addDay()->setTime(18, 0, 0),
        ]);

        BreakTime::create([
            'attendance_id' => $attendnace2->id,
            'break_start' => Carbon::now()->addDay()->setTime(12, 0, 0),
            'break_end' => Carbon::now()->addDay()->setTime(13, 0, 0),
        ]);

        $admin = Admin::factory()->create()->first();

        $day = Carbon::now()->addDay()->format('Y-m-d');
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/list?day={$day}");

        $response->assertSee(Carbon::now()->addDay()->format('Y/m/d'));

        $response->assertSeeInOrder([
            "{$user1->name}",
            "09:00",
            "17:00",
            "00:00",
            "08:00",
            "詳細",
        ]);

        $response->assertSeeInOrder([
            "{$user2->name}",
            "10:00",
            "18:00",
            "01:00",
            "07:00",
            "詳細",
        ]);
    }
}

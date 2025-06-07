<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class AdminDetailTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_attendance_datail()
    {
        $user = User::factory()->create()->first();
        $attendance=Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(17, 0, 0),
        ]);

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/{$attendance->id}");

        $response->assertSeeInOrder([
            "{$user->name}",
            Carbon::now()->format('Y'),
            Carbon::now()->format('m月d日'),
            "09:00",
            "17:00",
            "修正",
        ]);
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_clock_in_later_clock_out()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(17, 0, 0),
        ]);

        $admin = Admin::factory()->create()->first();
        $this->actingAs($admin, 'admin')->get("/admin/attendance/{$attendance->id}");

        try {
            $this->post('admin/attendance/correct', [
                'clock_in' => "18:00",
                'clock_out' => "17:00",
                'note' => 'テスト'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $commentError = $e->errors()['clock_in'][0];
            $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', $commentError);
            throw $e;
        }
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_break_start_later_clock_out()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(17, 0, 0),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);

        $admin = Admin::factory()->create()->first();
        $this->actingAs($admin, 'admin')->get("/admin/attendance/{$attendance->id}");

        try {
            $this->post('admin/attendance/correct', [
                'attendance_id' => $attendance->id,
                'clock_in' => "9:00",
                'clock_out' => "17:00",
                'break_start' => ['18:00'],
                'break_end' => ['13:00'],
                'note' => 'テスト'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $commentError = $e->errors()['break_start.0'][0];
            $this->assertEquals('休憩時間が勤務時間外です', $commentError);
            throw $e;
        }
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_break_end_later_clock_out()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(17, 0, 0),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);

        $admin = Admin::factory()->create()->first();
        $this->actingAs($admin, 'admin')->get("/admin/attendance/{$attendance->id}");

        try {
            $this->post('admin/attendance/correct', [
                'attendance_id' => $attendance->id,
                'clock_in' => "9:00",
                'clock_out' => "17:00",
                'break_start' => ['12:00'],
                'break_end' => ['18:00'],
                'note' => 'テスト'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $commentError = $e->errors()['break_end.0'][0];
            $this->assertEquals('休憩時間が勤務時間外です', $commentError);
            throw $e;
        }
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_validation_note()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(17, 0, 0),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);

        $admin = Admin::factory()->create()->first();
        $this->actingAs($admin, 'admin')->get("/admin/attendance/{$attendance->id}");

        try {
            $this->post('admin/attendance/correct', [
                'attendance_id' => $attendance->id,
                'clock_in' => "9:00",
                'clock_out' => "17:00",
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => ''
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $commentError = $e->errors()['note'][0];
            $this->assertEquals('備考を入力してください', $commentError);
            throw $e;
        }
    }
}

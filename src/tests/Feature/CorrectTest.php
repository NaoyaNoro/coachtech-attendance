<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrect;
use Illuminate\Support\Carbon;

class CorrectTest extends TestCase
{
    use RefreshDatabase;

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_clock_in_later_clock_out()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);
        $this->actingAs($user)->get("/attendance/{$attendance->id}");

        try{
            $this->post('attendance/correct',[
                'clock_in'=>"18:00",
                'clock_out'=>"17:00",
                'note'=>'テスト'
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
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        BreakTime::create([
            'attendance_id'=>$attendance->id,
            'break_start'=> Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);
        $this->actingAs($user)->get("/attendance/{$attendance->id}");

        try {
            $this->post('attendance/correct', [
                'attendance_id' => $attendance->id,
                'clock_in'=> '09:00',
                'clock_out' => '17:00',
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

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_break_end_later_clock_out()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);
        $this->actingAs($user)->get("/attendance/{$attendance->id}");

        try {
            $this->post('attendance/correct', [
                'attendance_id' => $attendance->id,
                'clock_in' => '09:00',
                'clock_out' => '17:00',
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
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);
        $this->actingAs($user)->get("/attendance/{$attendance->id}");

        try {
            $this->post('attendance/correct', [
                'attendance_id' => $attendance->id,
                'clock_in' => '09:00',
                'clock_out' => '17:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => ''
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $commentError = $e->errors()['note'][0];
            $this->assertEquals('備考を記入してください', $commentError);
            throw $e;
        }
    }

    // 修正申請処理が実行される
    public function test_correct_attendance()
    {
        Carbon::setTestNow(Carbon::parse('2025-05-25'));

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);
        $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $this->post('attendance/correct', [
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'note' => 'テスト'
        ]);

        $response = $this->actingAs($user)->get("/stamp_correction_request/list");
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "承認待ち",
            "{$user->name}",
            "2025/05/25",
            "テスト",
            "2025/05/25",
            "詳細",
        ]);

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/stamp_correction_request/list");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "承認待ち",
            "{$user->name}",
            "2025/05/25",
            "テスト",
            "2025/05/25",
            "詳細",
        ]);
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_unapproved_application()
    {
        $user = User::factory()->create()->first();
        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'clock_in' =>  Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $break1=BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => Carbon::today()->setTime(12, 0, 0),
            'break_end' => Carbon::today()->setTime(13, 0, 0),
        ]);

        $this->actingAs($user)->post('attendance/correct', [
            'attendance_id' => $attendance1->id,
            'break_id'=>$break1->id,
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'note' => 'テスト'
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::yesterday()->setTime(7, 0, 0),
            'clock_out' => Carbon::yesterday()->setTime(15, 0, 0),
        ]);

        $break2=BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => Carbon::yesterday()->setTime(10, 0, 0),
            'break_end' => Carbon::yesterday()->setTime(11, 0, 0),
        ]);

        $this->actingAs($user)->post('attendance/correct', [
            'attendance_id' => $attendance2->id,
            'break_id' => $break2->id,
            'clock_in' => '08:00',
            'clock_out' => '16:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => 'テスト2'
        ]);

        $response = $this->actingAs($user)->get("/stamp_correction_request/list");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "承認待ち",
            "{$user->name}",
            Carbon::today()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",

            "承認待ち",
            "{$user->name}",
            Carbon::yesterday()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",
        ]);
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_approved_application()
    {
        $user = User::factory()->create()->first();
        $admin = Admin::factory()->create()->first();

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'clock_in' =>  Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $break1 = BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => Carbon::today()->setTime(12, 0, 0),
            'break_end' => Carbon::today()->setTime(13, 0, 0),
        ]);

        $this->actingAs($user)->post('attendance/correct', [
            'attendance_id' => $attendance1->id,
            'break_id' => $break1->id,
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'note' => 'テスト'
        ]);
        AttendanceCorrect::where('attendance_id', $attendance1->id)->update([
            'approval' => 'approved',
            'admin_id' => $admin->id,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::yesterday()->setTime(7, 0, 0),
            'clock_out' => Carbon::yesterday()->setTime(15, 0, 0),
        ]);

        $break2 = BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => Carbon::yesterday()->setTime(10, 0, 0),
            'break_end' => Carbon::yesterday()->setTime(11, 0, 0),
        ]);

        $this->actingAs($user)->post('attendance/correct', [
            'admin_id' => $admin->id,
            'approval' => 'approved',
            'attendance_id' => $attendance2->id,
            'break_id' => $break2->id,
            'clock_in' => '08:00',
            'clock_out' => '16:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => 'テスト2'
        ]);
        AttendanceCorrect::where('attendance_id', $attendance2->id)->update([
            'approval' => 'approved',
            'admin_id' => $admin->id,
        ]);

        $response = $this->actingAs($user)->get("/stamp_correction_request/list?tab=approved");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "承認済み",
            "{$user->name}",
            Carbon::today()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",

            "承認済み",
            "{$user->name}",
            Carbon::yesterday()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",
        ]);
    }

    // 各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function test_detail_button(){
        Carbon::setTestNow(Carbon::parse('2025-05-25'));

        $user = User::factory()->create()->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2025-05-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-05-25 17:00:00'),
        ]);

        $break=BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-05-25 12:00:00'),
            'break_end' => Carbon::parse('2025-05-25 13:00:00'),
        ]);
        $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $this->post('attendance/correct', [
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'break_id' => [$break->id],
            'note' => 'テスト'
        ]);

        $response=$this->actingAs($user)->get("/stamp_correction_request/list");

        $response->assertSee("詳細");

        $response=$this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSeeInOrder([
            "{$user->name}",
            "2025年",
            "05月25日",
            "10:00",
            "18:00",
            "13:00",
            "14:00",
            "承認待ちのため修正はできません",
        ]);
    }
}

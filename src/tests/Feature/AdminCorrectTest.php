<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrect;
use App\Models\breakCorrect;
use App\Models\ClockOutCorrect;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminCorrectTest extends TestCase
{
    use RefreshDatabase;

    // 承認待ちの修正申請が全て表示されている
    public function test_unapproved_application()
    {
        $users = User::factory()->count(2)->create();
        $user1 = $users[0];
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'clock_in' =>  Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $break1 = BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => Carbon::today()->setTime(12, 0, 0),
            'break_end' => Carbon::today()->setTime(13, 0, 0),
        ]);

        $this->actingAs($user1)->post('attendance/correct', [
            'attendance_id' => $attendance1->id,
            'break_id' => [$break1->id],
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'note' => 'テスト'
        ]);

        $user2 = $users[1];
        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::yesterday()->setTime(7, 0, 0),
            'clock_out' => Carbon::yesterday()->setTime(15, 0, 0),
        ]);

        $break2 = BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => Carbon::yesterday()->setTime(10, 0, 0),
            'break_end' => Carbon::yesterday()->setTime(11, 0, 0),
        ]);

        $this->actingAs($user2)->post('attendance/correct', [
            'attendance_id' => $attendance2->id,
            'break_id' => [$break2->id],
            'clock_in' => '08:00',
            'clock_out' => '16:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => 'テスト2'
        ]);

        $admin = Admin::factory()->create()->first();

        $response = $this->actingAs($admin, 'admin')->get("/stamp_correction_request/list?tab=unApproved");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "承認待ち",
            "{$user1->name}",
            Carbon::today()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",

            "承認待ち",
            "{$user2->name}",
            Carbon::yesterday()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",
        ]);
    }

    // 承認済みの修正申請が全て表示されている
    public function test_approved_application()
    {
        $users = User::factory()->count(2)->create();
        $user1=$users[0];
        $admin = Admin::factory()->create()->first();

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'clock_in' =>  Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $break1 = BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => Carbon::today()->setTime(12, 0, 0),
            'break_end' => Carbon::today()->setTime(13, 0, 0),
        ]);

        $this->actingAs($user1)->post('attendance/correct', [
            'attendance_id' => $attendance1->id,
            'break_id' => [$break1->id],
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

        $user2 = $users[1];
        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::yesterday()->setTime(7, 0, 0),
            'clock_out' => Carbon::yesterday()->setTime(15, 0, 0),
        ]);

        $break2 = BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => Carbon::yesterday()->setTime(10, 0, 0),
            'break_end' => Carbon::yesterday()->setTime(11, 0, 0),
        ]);

        $this->actingAs($user2)->post('attendance/correct', [
            'admin_id' => $admin->id,
            'approval' => 'approved',
            'attendance_id' => $attendance2->id,
            'break_id' => [$break2->id],
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

        $response = $this->actingAs($admin, 'admin')->get("/stamp_correction_request/list?tab=approved");

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            "承認済み",
            "{$user1->name}",
            Carbon::today()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",

            "承認済み",
            "{$user2->name}",
            Carbon::yesterday()->format('Y/m/d'),
            "テスト",
            Carbon::today()->format('Y/m/d'),
            "詳細",
        ]);
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_approve_detail()
    {
        $user = User::factory()->create()->first();
        $admin = Admin::factory()->create()->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' =>  Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(13, 0, 0),
            'break_end' => Carbon::today()->setTime(14, 0, 0),
        ]);

        $this->actingAs($user)->post('attendance/correct', [
            'attendance_id' => $attendance->id,
            'break_id' => [$break->id],
            'clock_in' => '08:00',
            'clock_out' => '16:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => 'テスト'
        ]);

        $correction = AttendanceCorrect::where('attendance_id', $attendance->id)->latest()->first();
        $correctionId = $correction->id;

        $response = $this->actingAs($admin, 'admin')->get("/stamp_correction_request/approve/$correctionId");

        $response->assertSeeInOrder([
            "{$user->name}",
            Carbon::today()->format('Y年'),
            Carbon::today()->format('m月d日'),
            "08:00",
            "16:00",
            "12:00",
            "13:00",
            "テスト"
        ]);
    }

    // 修正申請の承認処理が正しく行われる
    public function test_approval_process_execution()
    {
        $user = User::factory()->create()->first();
        $admin = Admin::factory()->create()->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' =>  Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(13, 0, 0),
            'break_end' => Carbon::today()->setTime(14, 0, 0),
        ]);

        $this->actingAs($user)->post('attendance/correct', [
            'attendance_id' => $attendance->id,
            'break_id' => [$break->id],
            'clock_in' => '08:00',
            'clock_out' => '16:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => 'テスト'
        ]);

        $correction = AttendanceCorrect::where('attendance_id', $attendance->id)->latest()->first();
        $correctionId = $correction->id;

        $this->actingAs($admin, 'admin')->post("/admin/approve/$correctionId",[
            'attendance_correct_id'=> $correctionId,
            'attendance_id' => $attendance->id,
            'break_id' => [$break->id],
        ]);

        Auth::guard('admin')->logout();

        $response=$this->actingAs($admin, 'admin')->get("/admin/attendance/{$attendance->id}");

        $response->assertSeeInOrder([
            "勤怠詳細",
            "{$user->name}",
            Carbon::today()->format('Y年'),
            Carbon::today()->format('m月d日'),
            "08:00",
            "16:00",
            "12:00",
            "13:00",
        ]);
    }
}

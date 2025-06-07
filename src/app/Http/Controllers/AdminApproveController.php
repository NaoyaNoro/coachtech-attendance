<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\AttendanceCorrect;
use App\Models\ClockInCorrect;
use App\Models\ClockOutCorrect;
use App\Models\BreakTime;
use App\Models\BreakCorrect;
use App\Models\BreakAdd;

class AdminApproveController extends Controller
{
    //管理者の申請一覧の取得
    public function approve_list(Request $request)
    {
        $isAdmin=Auth::guard('admin')->check();
        $activeTab = $request->query('tab', 'unApproved');

        $query = AttendanceCorrect::with('attendance.user');

        if ($activeTab === 'approved') {
            $query->where('approval', 'approved');
        } else {
            $query->where('approval', 'pending');
        }

        $attendanceCorrects = $query->get();
        return view('apply-list', compact('isAdmin', 'attendanceCorrects', 'activeTab'));
    }

    //管理者の承認画面表示
    public function confirm_approval(Request $request)
    {
        $attendanceCorrectId=$request->attendance_correct_request;
        $attendanceCorrect=AttendanceCorrect::with(['user','attendance'])->find($attendanceCorrectId);
        $attendance=Attendance::find($attendanceCorrect->attendance_id);

        $userName = $attendanceCorrect->user->name;
        $carbon = Carbon::parse($attendanceCorrect->attendance->clock_in);
        $year = $carbon->format('Y') . '年';
        $day = $carbon->format('m') . '月' . $carbon->format('d') . '日';

        $clockIn=ClockInCorrect::where('attendance_correct_id', $attendanceCorrectId)->first();
        $clock_in= $clockIn ? Carbon::parse($clockIn->requested_time) : $attendance->clock_in;

        $clockOut = ClockOutCorrect::where('attendance_correct_id', $attendanceCorrectId)->first();
        $clock_out = $clockOut ? Carbon::parse($clockOut->requested_time) : $attendance->clock_out;

        $breaks = $attendance ? BreakTime::where('attendance_id', $attendance->id)->get() : collect();


        foreach($breaks as $break)
        {
            $break_id=$break->id;
            $breakCorrect=BreakCorrect::where('break_id', $break_id)->where('attendance_correct_id', $attendanceCorrectId)->first();

            if($breakCorrect){
                $break->break_start = $breakCorrect->requested_start ?? $break->break_start;
                $break->break_end = $breakCorrect->requested_end ?? $break->break_end;
            }
        }

        $breakAdd=BreakAdd::where('attendance_correct_id', $attendanceCorrectId)->first();

        $approval=$attendanceCorrect->approval;

        return view('admin.admin-approve', compact('userName','year','day','clock_in','clock_out','breaks','attendanceCorrect','breakAdd','approval'));
    }

    public function approve(Request $request)
    {
        $attendanceCorrectId = $request->attendance_correct_request;
        $attendanceCorrect=AttendanceCorrect::find($attendanceCorrectId);

        $attendance = Attendance::find($attendanceCorrect->attendance_id);

        $clockIn=ClockInCorrect::where('attendance_correct_id', $attendanceCorrectId)->first();
        if($clockIn){
            $attendance->update([
                'clock_in'=>$clockIn->requested_time,
            ]);
        }

        $clockOut = ClockOutCorrect::where('attendance_correct_id', $attendanceCorrectId)->first();
        if ($clockOut) {
            $attendance->update([
                'clock_out' => $clockOut->requested_time,
            ]);
        }

        $breaks=BreakTime::where('attendance_id', $attendance->id)->get();
        foreach($breaks as $break){
            $breakCorrect=BreakCorrect::where('break_id',$break->id)->first();
            if ($breakCorrect) {
                $break->update([
                    'break_start' => $breakCorrect->requested_start ?? $break->break_start,
                    'break_end'   => $breakCorrect->requested_end ?? $break->break_end,
                ]);
            }
        }

        $breakAdd=BreakAdd::where('attendance_correct_id',$attendanceCorrectId)->first();
        if($breakAdd){
            BreakTime::create([
                'attendance_id'=>$attendance->id,
                'break_start'=>$breakAdd->add_start,
                'break_end'=>$breakAdd->add_end
            ]);
        }

        $attendanceCorrect->update([
            'approval' => 'approved',
            'admin_id' => Auth::guard('admin')->id()
        ]);

        return redirect('stamp_correction_request/list');
    }
}

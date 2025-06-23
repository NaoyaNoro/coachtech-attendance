<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrect;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use App\Models\ClockInCorrect;
use App\Models\ClockOutCorrect;
use App\Models\BreakCorrect;
use App\Models\BreakAdd;

class AttendanceController extends Controller
{
    public function attendance_list(Request $request)
    {
        $month = $request->query('month') ? Carbon::parse($request->query('month'))->startOfMonth() : Carbon::now()->startOfMonth();

        $displayMonth = $month->format('Y/m');
        $prevMonth = $month->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $month->copy()->addMonthNoOverflow()->format('Y-m');

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $days = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::where('user_id', auth()->id())->whereBetween('clock_in', [$startOfMonth, $endOfMonth])->get();
        $dayItems = [];

        foreach ($days as $day) {
            $attendance = $attendances->first(function ($att) use ($day) {
                return $att->clock_in->format('Y-m-d') === $day->format('Y-m-d');
            });
            if($attendance){
                $breaks = BreakTime::where('attendance_id', $attendance->id)->get();
                $break_sum = 0;
                foreach ($breaks as $break) {
                    // $start = $break->break_start;
                    // $end = $break->break_end;
                    if($break->break_end){
                        $start = $break->break_start->copy()->floorMinute();
                        $end = $break->break_end->copy()->floorMinute();
                        $break_minutes = $end->diffInMinutes($start);
                    }else{
                        $break_minutes=0;
                    }
                    $break_sum += $break_minutes;
                }
                $hours = floor($break_sum / 60);
                $minutes = $break_sum % 60;
                $break_time = sprintf('%02d:%02d', $hours, $minutes);

            }else{
                $break_time=null;
            }

            if($attendance && $attendance->clock_out){
                $clockIn = Carbon::parse($attendance->clock_in->format('H:i'));
                $clockOut = Carbon::parse($attendance->clock_out->format('H:i'));

                $working_sum=$clockOut->diffInMinutes($clockIn)-$break_sum;
                $hours = floor($working_sum / 60);
                $minutes = $working_sum % 60;
                $working_time = sprintf('%02d:%02d', $hours, $minutes);
            }else{
                $working_time=null;
            }
            $dayItems[] = [
                'date' => $day->format('Y-m-d'),
                'weekday' => $day->format('w'),
                'clock_in' => optional($attendance)->clock_in,
                'clock_out' => optional($attendance)->clock_out,
                'break_time'=>$break_time,
                'working_time'=>$working_time,
                'attendance_id'=>optional($attendance)->id,
            ];
        }
        return view('attendance-list',compact('month', 'displayMonth','prevMonth','nextMonth','dayItems'));
    }

    public function attendance_detail(Request $request)
    {
        $attendance_id=$request->id;

        $attendance=Attendance::with('user')->find($attendance_id);

        $isUnApproved=AttendanceCorrect::where('attendance_id', $attendance->id)->where('approval','pending')->exists();

        $isApproved = AttendanceCorrect::where('attendance_id', $attendance->id)->where('approval', 'approved')->exists();

        $userName= auth()->user()->name;
        $carbon = Carbon::parse($attendance['clock_in']);
        $year = $carbon->format('Y') . '年';
        $day = $carbon->format('m') . '月' . $carbon->format('d') . '日';

        $attendanceCorrectId = null;
        if($isUnApproved){
            $attendanceCorrectId=AttendanceCorrect::where('attendance_id', $attendance->id)->where('approval','pending')->value('id');
        }elseif($isApproved){
            $attendanceCorrectId = AttendanceCorrect::where('attendance_id', $attendance->id)->where('approval', 'approved')->value('id');
        }

        $clockIn = ClockInCorrect::where('attendance_correct_id', $attendanceCorrectId)->first();
        $clock_in = $clockIn ? Carbon::parse($clockIn->requested_time) : $attendance->clock_in;

        $clockOut = ClockOutCorrect::where('attendance_correct_id', $attendanceCorrectId)->first();
        $clock_out = $clockOut ? Carbon::parse($clockOut->requested_time) : $attendance->clock_out;

        $breaks = $attendance ? BreakTime::where('attendance_id', $attendance->id)->get() : collect();

        foreach ($breaks as $break) {
            $break_id = $break->id;
            $breakCorrect = BreakCorrect::where('break_id', $break_id)->where('attendance_correct_id',$attendanceCorrectId)->first();

            if ($breakCorrect) {
                $break->break_start = $breakCorrect->requested_start ?? $break->break_start;
                $break->break_end = $breakCorrect->requested_end ?? $break->break_end;
            }
        }

        $breakAdd = BreakAdd::where('attendance_correct_id', $attendanceCorrectId)->first();

        $noteCorrect = null;
        if($attendanceCorrectId){
            $noteCorrect=AttendanceCorrect::find($attendanceCorrectId)->note;
        }
        $note=$attendance->note;

        return view('attendance-detail',compact('userName','clock_in','clock_out','year','day','breaks','attendance_id', 'breakAdd','isUnApproved', 'isApproved','note','noteCorrect'));
    }
}

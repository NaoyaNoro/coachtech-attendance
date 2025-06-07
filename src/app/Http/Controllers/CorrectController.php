<?php

namespace App\Http\Controllers;


use App\Models\Attendance;
use App\Models\AttendanceCorrect;
use App\Models\ClockInCorrect;
use App\Models\ClockOutCorrect;
use App\Models\BreakTime;
use App\Models\BreakCorrect;
use Illuminate\Support\Carbon;
use App\Http\Requests\CorrectRequest;
use App\Models\BreakAdd;

class CorrectController extends Controller
{
    public function coorect_request(CorrectRequest $request)
    {
        $attendance = Attendance::find($request->attendance_id);
        $attendanceCorrect = AttendanceCorrect::where('user_id', auth()->id())->where('attendance_id', $attendance->id)->where('approval', 'pending')->first();

        if (!$attendanceCorrect) {
            $attendanceCorrect = AttendanceCorrect::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attendance->id,
                'note' => $request->note
            ]);
        }

        if($request->clock_in !== Carbon::parse($attendance->clock_in)->format('H:i')){
            $requested_time = Carbon::parse($attendance->clock_in)
                ->setTimeFromTimeString($request->clock_in);
            ClockInCorrect::create([
                'attendance_correct_id'=>$attendanceCorrect->id,
                'default_time'=>$attendance->clock_in,
                'requested_time'=>$requested_time
            ]);
        }

        if ($request->clock_out !== Carbon::parse($attendance->clock_out)->format('H:i')) {
            $requested_time = Carbon::parse($attendance->clock_out)
                ->setTimeFromTimeString($request->clock_out);
            ClockOutCorrect::create([
                'attendance_correct_id' => $attendanceCorrect->id,
                'default_time' => $attendance->clock_in,
                'requested_time' => $requested_time
            ]);
        }

        if(is_array($request->break_id)){
            for ($i = 0; $i < count($request->break_id); $i++) {
                $break = BreakTime::find($request->break_id[$i]);

                $defaultStart = Carbon::parse($break->break_start)->format('H:i');
                $defaultEnd   = Carbon::parse($break->break_end)->format('H:i');

                $inputStart = $request->break_start[$i];
                $inputEnd   = $request->break_end[$i];

                $isStartChanged = $inputStart !== $defaultStart;
                $isEndChanged   = $inputEnd !== $defaultEnd;

                if ($isStartChanged || $isEndChanged) {
                    BreakCorrect::create([
                        'attendance_correct_id' => $attendanceCorrect->id,
                        'break_id' => $break->id,
                        'default_start' => $break->break_start,
                        'default_end' => $break->break_end,
                        'requested_start' => $isStartChanged
                            ? Carbon::parse($break->break_start)->setTimeFromTimeString($inputStart)
                            : null,
                        'requested_end' => $isEndChanged
                            ? Carbon::parse($break->break_end)->setTimeFromTimeString($inputEnd)
                            : null,
                    ]);
                }
            }
        }

        if($request->break_start_add && $request->break_end_add){
            $add_start = Carbon::parse($attendance->clock_in)
                ->setTimeFromTimeString($request->break_start_add);
            $add_end = Carbon::parse($attendance->clock_in)
                ->setTimeFromTimeString($request->break_end_add);
            BreakAdd::create([
                'attendance_correct_id' => $attendanceCorrect->id,
                'add_start' => $add_start,
                'add_end' => $add_end,
            ]);
        }

        return redirect('/attendance/list');
    }
}


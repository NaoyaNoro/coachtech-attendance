<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceCorrect;
use App\Models\BreakTime;


class AdminCorrectController extends Controller
{
    public function attendance_detail(Request $request)
    {
        $attendance_id = $request->id;

        $attendance = Attendance::with('user')->find($attendance_id);

        $userName = $attendance->user->name;
        $carbon = Carbon::parse($attendance['clock_in']);
        $year = $carbon->format('Y') . '年';
        $day = $carbon->format('m') . '月' . $carbon->format('d') . '日';


        $clock_in = $attendance ? $attendance->clock_in : '';
        $clock_out = $attendance ? $attendance->clock_out : '';
        $breaks = $attendance ? BreakTime::where('attendance_id', $attendance->id)->get() : '';

        $note=$attendance->note;

        $isAttendanceCorrect=AttendanceCorrect::where('attendance_id', $attendance_id)->exists();

        return view('admin.attendance-detail',compact('attendance_id','userName','carbon','year','day','clock_in','clock_out','breaks','note','isAttendanceCorrect'));
    }

    public function admin_correct(CorrectRequest $request)
    {
        $attendance = Attendance::find($request->attendance_id);

        if ($request->clock_in !== Carbon::parse($attendance->clock_in)->format('H:i')) {
            $requested_time = Carbon::parse($attendance->clock_in)
                ->setTimeFromTimeString($request->clock_in);
            $attendance->update([
                'clock_in'=>$requested_time,
            ]);
        }

        if ($request->clock_out && ($attendance->clock_out === null || $request->clock_out !== Carbon::parse($attendance->clock_out)->format('H:i'))) {
            $requested_time = Carbon::parse($attendance->clock_in)
                ->setTimeFromTimeString($request->clock_out);

            $attendance->update([
                'clock_out' => $requested_time,
            ]);
        }

        if(!empty($request->break_id)){
            for ($i = 0; $i < count($request->break_id); $i++) {
                $break = BreakTime::find($request->break_id[$i]);

                $defaultStart = Carbon::parse($break->break_start)->format('H:i');
                $defaultEnd   = Carbon::parse($break->break_end)->format('H:i');

                $inputStart = $request->break_start[$i];
                $inputEnd   = $request->break_end[$i];

                $isStartChanged = $inputStart !== $defaultStart;
                $isEndChanged   = $inputEnd !== $defaultEnd;


                if ($isStartChanged || $isEndChanged) {
                    $date = Carbon::parse($break->break_start)->format('Y-m-d');

                    $newStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $inputStart);
                    $newEnd   = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $inputEnd);

                    $break->update([
                        'break_start' => $inputStart,
                        'break_end' => $inputEnd,
                    ]);
                }
            }
        }

        if ($request->break_start_add && $request->break_end_add) {
            $add_start = Carbon::parse($attendance->clock_in)
                ->setTimeFromTimeString($request->break_start_add);
            $add_end = Carbon::parse($attendance->clock_in)
                ->setTimeFromTimeString($request->break_end_add);
            BreakTime::create([
                'attendance_id'=> $attendance->id,
                'break_start' => $add_start,
                'break_end' => $add_end,
            ]);
        }

        $attendance->update([
            'note'=>$request->note,
        ]);

        return redirect('/admin/attendance/month/list/'.$attendance->user_id);
    }
}

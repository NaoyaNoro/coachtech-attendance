<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Response;

class AdminAttendanceController extends Controller
{
    //管理者の本日の勤怠状況の管理
    public function admin_list(Request $request)
    {
        $day = $request->query('day', Carbon::now()->format('Y-m-d'));
        $displayDay = Carbon::createFromFormat('Y-m-d', $day)->format('Y/m/d');
        $prevDay = Carbon::createFromFormat('Y-m-d', $day)->subDay()->format('Y-m-d');
        $nextDay = Carbon::createFromFormat('Y-m-d', $day)->addDay()->format('Y-m-d');

        $attendances=Attendance::whereDate('clock_in',Carbon::parse($day))->get();

        $dayItems=[];

        foreach ($attendances as $attendance){
            $breaks = BreakTime::where('attendance_id', $attendance->id)->get();
            $break_sum = 0;
            foreach ($breaks as $break) {
                $start = $break->break_start;
                $end = $break->break_end;
                if ($end) {
                    $break_minutes = $end->diffInMinutes($start);
                } else {
                    $break_minutes = 0;
                }
                $break_sum += $break_minutes;
            }
            $hours = floor($break_sum / 60);
            $minutes = $break_sum % 60;
            $break_time = sprintf('%02d:%02d', $hours, $minutes);

            if ($attendance && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in->format('H:i'));
                $clockOut = Carbon::parse($attendance->clock_out->format('H:i'));

                $working_sum = $clockOut->diffInMinutes($clockIn) - $break_sum;
                $hours = floor($working_sum / 60);
                $minutes = $working_sum % 60;
                $working_time = sprintf('%02d:%02d', $hours, $minutes);
            } else {
                $working_time = null;
            }

            $dayItems[]=[
                'name'=>$attendance->user->name,
                'clock_in'=>optional($attendance)->clock_in,
                'clock_out'=> optional($attendance)->clock_out,
                'break_time'=>$break_time,
                'working_time'=>$working_time,
                'attendance_id'=>$attendance->id,
            ];
        }
        return view('admin.admin-list',compact('displayDay', 'prevDay','nextDay','dayItems'));
    }

    //管理者のスタッフ一覧の表示
    public function staff_list(Request $request)
    {
        $users=User::all();
        return view('admin.staff-list',compact('users'));
    }

    //ユーザーの月次の表示
    public function staff_attendance_list(Request $request)
    {
        $month=$request->query('month') ? Carbon::parse($request->query('month'))->startOfMonth() : Carbon::now()->startOfMonth();

        $displayMonth = $month->format('Y/m');
        $prevMonth = $month->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $month->copy()->addMonthNoOverflow()->format('Y-m');

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $days = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::where('user_id', $request->id)->whereBetween('clock_in', [$startOfMonth, $endOfMonth])->get();
        $dayItems = [];

        $user=User::find($request->id);

        foreach ($days as $day) {
            $attendance = $attendances->first(function ($att) use ($day) {
                return $att->clock_in->format('Y-m-d') === $day->format('Y-m-d');
            });
            if ($attendance) {
                $breaks = BreakTime::where('attendance_id', $attendance->id)->get();
                $break_sum = 0;
                foreach ($breaks as $break) {
                    $start = $break->break_start;
                    $end = $break->break_end;
                    if ($end) {
                        $break_minutes = $end->diffInMinutes($start);
                    } else {
                        $break_minutes = 0;
                    }
                    $break_sum += $break_minutes;
                }
                $hours = floor($break_sum / 60);
                $minutes = $break_sum % 60;
                $break_time = sprintf('%02d:%02d', $hours, $minutes);
            } else {
                $break_time = null;
            }

            if ($attendance && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in->format('H:i'));
                $clockOut = Carbon::parse($attendance->clock_out->format('H:i'));

                $working_sum = $clockOut->diffInMinutes($clockIn) - $break_sum;
                $hours = floor($working_sum / 60);
                $minutes = $working_sum % 60;
                $working_time = sprintf('%02d:%02d', $hours, $minutes);
            } else {
                $working_time = null;
            }
            $dayItems[] = [
                'date' => $day->format('Y-m-d'),
                'weekday' => $day->format('w'),
                'clock_in' => optional($attendance)->clock_in,
                'clock_out' => optional($attendance)->clock_out,
                'break_time' => $break_time,
                'working_time' => $working_time,
                'attendance_id' => optional($attendance)->id,
            ];
        }
        return view('attendance-list', compact('user','month', 'displayMonth', 'prevMonth', 'nextMonth', 'dayItems'));
    }

    public function export_csv(Request $request) {

        $monthInput = $request->query('month');

        $month = $monthInput ? Carbon::parse($monthInput)->format('Y-m'): Carbon::now()->format('Y-m');

        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        $days = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::where('user_id', $request->id)->whereBetween('clock_in', [$startOfMonth, $endOfMonth])->get();

        $user = User::find($request->id);

        $dayItems = [];

        foreach ($days as $day) {
            $attendance = $attendances->first(function ($att) use ($day) {
                return $att->clock_in->format('Y-m-d') === $day->format('Y-m-d');
            });
            if ($attendance) {
                $breaks = BreakTime::where('attendance_id', $attendance->id)->get();
                $break_sum = 0;
                foreach ($breaks as $break) {
                    $start = $break->break_start;
                    $end = $break->break_end;
                    if ($end) {
                        $break_minutes = $end->diffInMinutes($start);
                    } else {
                        $break_minutes = 0;
                    }
                    $break_sum += $break_minutes;
                }
                $hours = floor($break_sum / 60);
                $minutes = $break_sum % 60;
                $break_time = sprintf('%02d:%02d', $hours, $minutes);
            } else {
                $break_time = null;
            }

            if ($attendance && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in->format('H:i'));
                $clockOut = Carbon::parse($attendance->clock_out->format('H:i'));

                $working_sum = $clockOut->diffInMinutes($clockIn) - $break_sum;
                $hours = floor($working_sum / 60);
                $minutes = $working_sum % 60;
                $working_time = sprintf('%02d:%02d', $hours, $minutes);
            } else {
                $working_time = null;
            }
            $dayItems[] = [
                'date' => $day->format('m/d'),
                'weekday' => $day->format('w'),
                'clock_in' => optional($attendance)->clock_in,
                'clock_out' => optional($attendance)->clock_out,
                'break_time' => $break_time,
                'working_time' => $working_time,
            ];
        }

        //csv出力処理
        $filename = "{$user->name}_{$month}.csv";
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['日付', '出勤時刻', '退勤時刻', '休憩時間', '合計']);

        foreach ($dayItems as $item) {
            $weekday = ['日', '月', '火', '水', '木', '金', '土'][$item['weekday']];
            $formattedDate = $item['date'] . "({$weekday})";
            fputcsv($handle, [
                $formattedDate,
                optional($item['clock_in'])->format('H:i'),
                optional($item['clock_out'])->format('H:i'),
                $item['break_time'],
                $item['working_time'],
            ]);
        }

        rewind($handle);
        return Response::stream(function () use ($handle) {
            fpassthru($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);

    }
}

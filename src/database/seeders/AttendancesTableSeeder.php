<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::where('name', 'user1')->first();

        $user2 = User::where('name', 'user2')->first();

        $start = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $end = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        for ($date = $start; $date->lte($end); $date->addDay()) {

            if ($date->isWeekend()) {
                continue;
            }

            $attendance1=Attendance::create([
                'user_id' => $user1->id,
                'clock_in' => $date->copy()->setTime(9, 0, 0),
                'clock_out' => $date->copy()->setTime(18, 0, 0),
                'note' => " "
            ]);

            $attendance2=Attendance::create([
                'user_id' => $user2->id,
                'clock_in' => $date->copy()->setTime(9, 0, 0),
                'clock_out' => $date->copy()->setTime(18, 0, 0),
                'note' => " "
            ]);

            BreakTime::create([
                'attendance_id' => $attendance1->id,
                'break_start' => $date->copy()->setTime(12, 0, 0),
                'break_end' => $date->copy()->setTime(13, 0, 0),
            ]);

            BreakTime::create([
                'attendance_id' => $attendance2->id,
                'break_start' => $date->copy()->setTime(12, 0, 0),
                'break_end' => $date->copy()->setTime(13, 0, 0),
            ]);
        }
    }
}

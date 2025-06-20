<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\Status;

class WorkController extends Controller
{
    public function clock_in()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        Status::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => 'working']
        );

        $user->attendances()->create();

        return redirect('/');
    }

    public function clock_out()
    {
        $user = auth()->user();

        Status::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => 'after_working']
        );
        /** @var \App\Models\User $user */
        $attendance = $user->attendances()
            ->whereDate('clock_in', Carbon::today())
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if ($attendance) {
            $attendance->update(['clock_out' => now()]);
        }

        return redirect('/');
    }

    public function break_start()
    {
        $user = auth()->user();

        Status::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => 'breaking']
        );
        /** @var \App\Models\User $user */
        $attendance = $user->attendances()
            ->whereDate('clock_in', Carbon::today())
            ->latest()
            ->first();

        $attendance?->breaks()->create([
            'user_id' => $user->id,
            'break_start' => now(),
        ]);

        return redirect('/');
    }

    public function break_end()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        Status::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => 'working']
        );

        $attendance = $user->attendances()
            ->whereDate('clock_in', Carbon::today())
            ->latest()
            ->first();

        $break = $attendance?->breaks()
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($break) {
            $break->update(['break_end' => now()]);
        }

        return redirect('/');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrect;
use Illuminate\Support\Carbon;

class ApplyController extends Controller
{
    public function apply_list(Request $request)
    {
        $userName=auth()->user()->name;

        $activeTab = $request->query('tab', 'unApproved');

        $query = AttendanceCorrect::with('attendance')->where('user_id', auth()->id());

        if($activeTab==='approved'){
            $query->where('approval','approved');
        }else{
            $query->where('approval','pending');
        }

        $isAdmin = false;

        $attendanceCorrects = $query->get();
        return view('apply-list',compact('userName', 'attendanceCorrects','activeTab','isAdmin'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Status;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function login()
    {
        return view('auth.admin-login');
    }

    public function index()
    {
        $user_id = auth()->id();
        $alreadyCheckIn = Attendance::where('user_id', $user_id)->whereDate('clock_in', Carbon::today())->exists();
        if (!$alreadyCheckIn) {
            Status::updateOrCreate(
                ['user_id' => $user_id],
                ['status' => 'before_working']
            );
        }
        $status=Status::where('user_id',$user_id)->value('status');
        return view('index',compact('status'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

}

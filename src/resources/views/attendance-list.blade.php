@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp


@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection


@section('content')
<div class="attendance-list__contents">
    <div class="list__inner">
        <div class="list__ttl">
            @if(isset($user))
            <h2>{{$user->name}}さんの勤怠</h2>
            @else
            <h2>勤怠一覧</h2>
            @endif
        </div>
        <div class="list__mouth">
            @if(isset($user))
            <a class="prev__month" href="{{ route('admin.attendance.month.list', ['id' => $user->id]) }}?month={{ $prevMonth }}">← 前月</a>

            <div class="current__month">
                <i class="fa-solid fa-calendar-days"></i>{{ $displayMonth }}
            </div>

            <a class="next__month" href="{{ route('admin.attendance.month.list', ['id' => $user->id]) }}?month={{ $nextMonth }}">翌月 →</a>

            @else
            <a class="prev__month" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">← 前月</a>

            <div class="current__month">
                <i class="fa-solid fa-calendar-days"></i>{{ $displayMonth }}
            </div>

            <a class="next__month" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月 →</a>
            @endif
        </div>
        <div class="list__date">
            <table class="date__table">
                <tr>
                    <th>
                        日付
                    </th>
                    <th>
                        出勤
                    </th>
                    <th>
                        退勤
                    </th>
                    <th>
                        休憩
                    </th>
                    <th>
                        合計
                    </th>
                    <th>
                        詳細
                    </th>
                </tr>
                @php
                $week_day=['日','月','火','水','木','金','土']
                @endphp
                @for($i=0; $i < count($dayItems); $i++)
                    <tr>
                    <td>
                        {{Carbon::parse($dayItems[$i]['date'])->format('m/d')}}({{$week_day[$dayItems[$i]['weekday']]}})
                    </td>
                    <td>
                        {{$dayItems[$i]['clock_in'] ? Carbon::parse($dayItems[$i]['clock_in'])->format('H:i') :''}}
                    </td>
                    <td>
                        {{$dayItems[$i]['clock_out'] ? Carbon::parse($dayItems[$i]['clock_out'])->format('H:i') :''}}
                    </td>
                    <td>
                        {{$dayItems[$i]['break_time'] ? $dayItems[$i]['break_time']:''}}
                    </td>
                    <td>
                        {{$dayItems[$i]['working_time'] ? $dayItems[$i]['working_time']:''}}
                    </td>
                    @php
                    $isAdminView = isset($user);
                    $hasClockIn = $dayItems[$i]['clock_in'];
                    $attendanceId = $dayItems[$i]['attendance_id'];
                    @endphp
                    <td>
                        @if($hasClockIn)
                        @if($isAdminView)
                        <a class="attendnace__detail" href="/admin/attendance/{{ $attendanceId }}">詳細</a>
                        @else
                        <a class="attendnace__detail" href="/attendance/{{ $attendanceId }}">詳細</a>
                        @endif
                        @endif
                    </td>
                    </tr>
                    @endfor
            </table>
        </div>
        <div class="export-csv">
            @if(isset($user))
            <form action="/admin/attendance/export/csv" method="GET">
                <input type="hidden" name="id" value="{{ $user->id }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="export-csv__button">CSV出力</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp


@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection


@section('content')
<h2>{{$user->name}}さんの勤怠</h2>
<div class="attendance-list__contents">
    <div class="list__innner">
        <div class="list__mouth">
            <a href="{{ route('admin.attendance.month.list', ['id' => $user->id]) }}?month={{ $prevMonth }}">← 前月</a>
            <i class="fa-solid fa-calendar-days"></i>{{ $displayMonth }}
            <a href="{{ route('admin.attendance.month.list', ['id' => $user->id]) }}?month={{ $nextMonth }}">翌月 →</a>

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
                    <td>
                        @if($dayItems[$i]['clock_out'])
                        <a href="/admin/attendance/{{$dayItems[$i]['attendance_id'] }}">
                            詳細
                        </a>
                        @else

                        @endif
                    </td>
                    </tr>
                    @endfor
            </table>
        </div>
    </div>
</div>
@endsection
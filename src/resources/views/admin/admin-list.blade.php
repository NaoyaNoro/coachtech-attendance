@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin-list.css') }}">
@endsection


@section('content')
<div class="attendance-list__contents">
    <div class="list__inner">
        <div class="list__ttl">
            <h2>{{$displayDay}}の勤怠</h2>
        </div>
        <div class="list__day">
            <a href="{{ route('admin.attendance.list', ['day' => $prevDay]) }}" class="prev__day">← 前日</a>

            <div class="today">
                <i class="fa-solid fa-calendar-days"></i>{{ $displayDay }}
            </div>

            <a href="{{ route('admin.attendance.list', ['day' => $nextDay]) }}" class="next__day">翌日 →</a>
        </div>
        <div class="list__date">
            <table class="attendance__table">
                <tr>
                    <th>
                        名前
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
                @foreach($dayItems as $dayItem)
                <tr>
                    <td>
                        {{ $dayItem['name'] }}
                    </td>
                    <td>
                        {{ $dayItem['clock_in'] ? $dayItem['clock_in']->format('H:i') : ''}}
                    </td>
                    <td>
                        {{ $dayItem['clock_out'] ? $dayItem['clock_out']->format('H:i') : ''}}
                    </td>
                    <td>
                        {{ $dayItem['break_time'] }}
                    </td>
                    <td>
                        {{ $dayItem['working_time'] }}
                    </td>
                    <td>
                        <a href="/admin/attendance/{{$dayItem['attendance_id'] }}" class="detail__button">
                            詳細
                        </a>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
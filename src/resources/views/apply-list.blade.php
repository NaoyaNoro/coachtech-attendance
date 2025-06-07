@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/apply-list.css') }}">
@endsection


@section('content')
<div class="apply__content">
    <div class="apply__inner">
        <div class="apply__ttl">
            @if($isAdmin)
            <h2>申請一覧</h2>
            @else
            <h2>申請画面</h2>
            @endif
        </div>
        <div class="tab__navigation">
            <a href="{{url('/stamp_correction_request/list?tab=unApproved')}}" class="tab__button {{$activeTab==='unApproved'?'active':''}}">承認待ち</a>
            <a href="{{url('/stamp_correction_request/list?tab=approved')}}" class="tab__button {{$activeTab==='approved'?'active':''}}">承認済み</a>
        </div>
        <table class="apply__table">
            <tr>
                <th>
                    状態
                </th>
                <th>
                    名前
                </th>
                <th>
                    対象日時
                </th>
                <th>
                    申請理由
                </th>
                <th>
                    申請日時
                </th>
                <th>
                    詳細
                </th>
            </tr>
            @foreach($attendanceCorrects as $attendanceCorrect)
            <tr>
                <td>
                    {{$attendanceCorrect->approval==='pending' ? '承認待ち' : '承認済み'}}
                </td>
                <td>
                    @if($isAdmin)
                    {{$attendanceCorrect->attendance->user->name}}
                    @else
                    {{ $userName }}
                    @endif
                </td>
                <td>
                    {{ $attendanceCorrect->attendance->clock_in->format('Y/m/d') }}
                </td>
                <td>
                    {{$attendanceCorrect->note}}
                </td>
                <td>
                    {{$attendanceCorrect->created_at->format('Y/m/d')}}
                </td>
                <td>

                    @if($isAdmin)
                    <a href="/stamp_correction_request/approve/{{$attendanceCorrect->id }}" class=" detail__button">
                        詳細
                    </a>
                    @else
                    <a href="/attendance/{{$attendanceCorrect->attendance_id }}" class="detail__button">
                        詳細
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
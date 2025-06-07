@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection


@section('content')
<div class="attendance__content">
    <div class="attendnace__innner">
        <div class="attendance__status">
            <p class="status">
                @if($status=='before_working')
                勤務外
                @elseif($status=='working')
                出勤中
                @elseif($status=='breaking')
                休憩中
                @elseif($status=='after_working')
                退勤済
                @endif
            </p>
        </div>
        <div class="attendance__date">
            <p class="date">
                <span dusk="date-display" id="date"></span>
            </p>
        </div>
        <div class="attendance__time">
            <p class="time">
                <span dusk="time-display" id="time"></span>
            </p>
        </div>
        <div class="form__button">
            @if($status=='before_working')
            <form action="/clock_in" method="post">
                @csrf
                <button type="submit" class="attendance__button clock_in">出勤</button>
            </form>
            @elseif($status=='working')
            <div class="form__contents">
                <form action="/clock_out" method="post">
                    @csrf
                    <button type="submit" class="attendance__button clock_out">退勤</button>
                </form>
                <form action="/break_start" method="post">
                    @csrf
                    <button type="submit" class="attendance__button break_start">休憩入</button>
                </form>
            </div>
            @elseif($status=='breaking')
            <form action="/break_end" method="post">
                @csrf
                <button type="submit" class="attendance__button break_end">休憩戻</button>
            </form>
            @elseif($status=='after_working')
            <p class="after-working">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</div>
<script src="{{ asset('js/index.js') }}"></script>
@endsection
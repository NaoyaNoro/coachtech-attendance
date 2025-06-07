@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp


@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection


@section('content')
<div class="detail__content">
    <div class="detail__inner">
        <div class="detail__ttl">
            <h2>勤怠詳細</h2>
        </div>
        <form action="/admin/approve/{{$attendanceCorrect->id}}" class="correct__attendance" method="post">
            @csrf
            <table class="detail__table">
                <tr>
                    <th class="table__ttl">
                        名前
                    </th>
                    <td class="td__first">
                        {{ $userName }}
                    </td>
                    <td class="td__second">

                    </td>
                    <td class="td__third">

                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                <tr>
                    <th class="table__ttl">
                        日付
                    </th>
                    <td class="td__first">
                        {{ $year }}
                    </td>
                    <td class="td__second">

                    </td>
                    <td class="td__third">
                        {{ $day }}
                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                <tr>
                    <th class="table__ttl">
                        出勤・退勤
                    </th>
                    <td class="td__first">
                        <input type="text" name="clock_in" value="{{ $clock_in ? Carbon::parse($clock_in)->format('H:i') : '' }}" readonly>
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="clock_out" value="{{ $clock_in ? Carbon::parse($clock_out)->format('H:i') : '' }}" readonly>
                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                @error('clock_in')
                <tr class="form__error">
                    <td colspan="2">
                        {{ $message }}
                    </td>
                </tr>
                @enderror
                @error('clock_out')
                <tr class="form__error">
                    <td colspan="2">
                        {{ $message }}
                    </td>
                </tr>
                @enderror
                @if($breaks)
                @foreach($breaks as $break)
                <tr>
                    <th class="table__ttl">
                        休憩{{ $loop->iteration }}
                    </th>
                    <td class="td__first">
                        <input type="text" name="break_start[]" value="{{ Carbon::parse($break['break_start'])->format('H:i')}}" readonly>
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="break_end[]" value="{{Carbon::parse($break['break_end'])->format('H:i')}}" readonly>
                        <input type="hidden" name="break_id[]" value="{{$break['id']}}">
                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                @error("break_start.$loop->index")
                <tr class="form__error">
                    <td colspan="2">
                        {{ $message }}
                    </td>
                </tr>
                @enderror
                @error("break_end.$loop->index")
                <tr class="form__error">
                    <td colspan="2">
                        {{ $message }}
                    </td>
                </tr>
                @enderror
                @endforeach

                @if($approval==="pending")
                <tr>
                    <th class="table__ttl">
                        休憩{{ count($breaks)+1 }}
                    </th>
                    <td class="td__first">
                        <input type="text" name="break_start_add" value="{{ $breakAdd? Carbon::parse($breakAdd->add_start)->format('H:i') : '' }}" readonly>
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="break_end_add" value="{{ $breakAdd? Carbon::parse($breakAdd->add_end)->format('H:i') : '' }}" readonly>
                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                @endif
                @error("break_start_add")
                <tr class="form__error">
                    <td colspan="2">
                        {{ $message }}
                    </td>
                </tr>
                @enderror
                @error("break_end_add")
                <tr class="form__error">
                    <td colspan="2">
                        {{ $message }}
                    </td>
                </tr>
                @enderror

                @else
                <tr>
                    <th class="table__ttl">
                        休憩1
                    </th>
                    <td class="td__first">
                        <input type="text" name="break_start" value="">
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="break_end" value="">
                    </td>
                </tr>
                @endif
                <tr>
                    <th class="table__ttl">
                        備考
                    </th>
                    <td colspan="3">
                        <textarea name="note" rows="5" {{ old('note',$note ??'') }}>{{$attendanceCorrect->note}}</textarea>
                    </td>
                </tr>
                @error("note")
                <tr class="form__error">
                    <td colspan="2">
                        {{ $message }}
                    </td>
                </tr>
                @enderror
            </table>
            @if($approval==="pending")
            <button class="correct__button" type="submit">
                承認
            </button>
            @elseif($approval==="approved")
            <button class="correct__button approve" type="submit" disabled>
                承認済み
            </button>
            @endif
        </form>
    </div>
</div>
@endsection
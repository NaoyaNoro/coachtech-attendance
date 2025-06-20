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
            <h2>詳細画面</h2>
        </div>
        <form action="/attendance/correct" class="correct__attendance" method="post">
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
                <tr class="{{ $errors->has('clock_in') || $errors->has('clock_out') ? 'no-border' : '' }}">
                    <th class="table__ttl">
                        出勤・退勤
                    </th>
                    <td class="td__first">
                        <input type="text" name="clock_in" value="{{ $clock_in ? Carbon::parse($clock_in)->format('H:i') : '' }}" {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="clock_out" value="{{ $clock_out ? Carbon::parse($clock_out)->format('H:i') : '' }}" {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                @error('clock_in')
                <tr class="{{ $errors->has('clock_in') && $errors->has('clock_out') ? 'no-border' : '' }}">
                    <th>

                    </th>
                    <td colspan="3">
                        <p class="form__error">{{ $message }}</p>
                    </td>
                </tr>
                @enderror
                @error('clock_out')
                <tr class="form__error">
                    <th>

                    </th>
                    <td colspan="3">
                        <p class="form__error">{{ $message }}</p>
                    </td>
                </tr>
                @enderror
                @if($breaks)
                @foreach($breaks as $break)
                <tr class="{{ $errors->has("break_start.$loop->index") || $errors->has("break_end.$loop->index") ? 'no-border' : '' }}">
                    <th class="table__ttl">
                        休憩{{ $loop->iteration }}
                    </th>
                    <td class="td__first">
                        <input type="text" name="break_start[]" value="{{ $break['break_start'] ? Carbon::parse($break['break_start'])->format('H:i') : '' }}" {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="break_end[]" value="{{$break['break_end'] ? Carbon::parse($break['break_end'])->format('H:i') : ''}}" {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__empty">

                    </td>
                    <input type="hidden" name="break_id[]" value="{{$break['id']}}">
                </tr>
                @error("break_start.$loop->index")
                <tr class="{{ $errors->has("break_start.$loop->index") && $errors->has("break_end.$loop->index") ? 'no-border' : '' }}">
                    <th>

                    </th>
                    <td colspan="3">
                        <p class="form__error">{{ $message }}</p>
                    </td>
                </tr>
                @enderror
                @error("break_end.$loop->index")
                <tr>
                    <th>

                    </th>
                    <td colspan="3">
                        <p class="form__error">{{ $message }}</p>
                    </td>
                </tr>
                @enderror
                @endforeach
                <tr class="{{ $errors->has('break_start_add') || $errors->has('break_end_add') ? 'no-border' : '' }}">
                    <th class="table__ttl">
                        休憩{{ count($breaks)+1 }}
                    </th>
                    <td class="td__first">
                        <input type="text" name="break_start_add" value="{{$breakAdd ? Carbon::parse($breakAdd['add_start'])->format('H:i') : ''}} " {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="break_end_add" value="{{$breakAdd ? Carbon::parse($breakAdd['add_end'])->format('H:i') : ''}} " {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                @error("break_start_add")
                <tr class="{{ $errors->has('break_start_add') && $errors->has('break_end_add') ? 'no-border' : '' }}">
                    <th>

                    </th>
                    <td colspan="3">
                        <p class="form__error">{{ $message }}</p>
                    </td>
                </tr>
                @enderror
                @error("break_end_add")
                <tr>
                    <th>

                    </th>
                    <td colspan="3">
                        <p class="form__error">{{ $message }}</p>
                    </td>
                </tr>
                @enderror
                @else
                <tr>
                    <th class="table__ttl">
                        休憩1
                    </th>
                    <td class="td__first">
                        <input type="text" name="break_start" value="" {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__second">
                        〜
                    </td>
                    <td class="td__third">
                        <input type="text" name="break_end" value="" {{$isUnApproved ? 'readonly' : ''}}>
                    </td>
                    <td class="td__empty">

                    </td>
                </tr>
                @endif
                <tr>
                    <th class="table__ttl">
                        備考
                    </th>
                    <td colspan="3">
                        <textarea name="note" rows="5" {{$isUnApproved ? 'readonly' : ''}}>{{ old('note',$note ??'') }}</textarea>

                        @error("note")
                        <p class="form__error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>
            <input type="hidden" value="{{$attendance_id}}" name="attendance_id">
            @if($isUnApproved)
            <p class="waiting__approve">*承認待ちのため修正はできません。</p>
            @else
            <button class="correct__button" type="submit">
                修正
            </button>
            @endif
        </form>
    </div>
</div>
@endsection
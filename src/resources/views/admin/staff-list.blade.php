@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-list.css') }}">
@endsection


@section('content')
<div class="attendance-list__contents">
    <div class="list__inner">
        <div class="staff__ttl">
            <h2>スタッフ一覧</h2>
        </div>
        <table class="staff__table">
            <tr>
                <th>
                    名前
                </th>
                <th>
                    メールアドレス
                </th>
                <th>
                    月次勤務
                </th>
            </tr>
            @foreach($users as $user)
            <tr>
                <td>
                    {{$user->name}}
                </td>
                <td>
                    {{$user->email}}
                </td>
                <td>
                    <a href="/admin/attendance/month/list/{{$user->id}}" class="detail__button">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>


@endsection
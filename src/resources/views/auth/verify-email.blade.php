@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify__email.css') }}">
@endsection

@section('navigation')
{{-- ナビゲーションを表示しない --}}
@endsection

@section('content')
<div class="verify-email__content">
    <div class="verify-email__head">
        <h2>メール認証が必要です</h2>
    </div>
    <div class="verify-email__description">
        <p>登録していただいたメールアドレスに認証メールを送信しました。<br>メール認証を完了してください。</p>
    </div>
    <div class="verify-email__button">
        <a href="{{ env('MAILHOG_URL', 'http://localhost:8025') }}" class="verify-email__link" dusk="verify-link">
            認証はこちらから
        </a>
    </div>
    <div class="verify-email__button">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-email__resend">認証メールを再送信</button>
        </form>
    </div>
</div>

@endsection
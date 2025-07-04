@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('navigation')
{{-- ナビゲーションを表示しない --}}
@endsection

@section('content')
<div class="login__content">
    <div class="login__ttl">
        <h2>管理者ログイン</h2>
    </div>
    <div class="login__form">
        <form class="form" action="/admin/login" method="post">
            @csrf
            <div class="form__item">
                <div class="form__item-name">
                    <p class="form__label">
                        メールアドレス
                    </p>
                </div>
                <div class="form__item-input">
                    <input type="text" name="email" value="{{ old('email') }}" class="form__input">
                </div>
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__item">
                <div class="form__item-name">
                    <p class="form__label">
                        パスワード
                    </p>
                </div>
                <div class="form__item-input">
                    <input type="password" name="password" class="form__input">
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">管理者ログインする</button>
            </div>
        </form>
    </div>
</div>
@endsection
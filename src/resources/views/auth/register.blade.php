@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('navigation')
{{-- ナビゲーションを表示しない --}}
@endsection

@section('content')
<div class="register__content">
    <div class="register__ttl">
        <h2>会員登録</h2>
    </div>
    <div class="register__form">
        <form class="form" action="/register" method="post">
            @csrf
            <div class="form__item">
                <div class="form__item-name">
                    <p class="form__label">
                        名前
                    </p>
                </div>
                <div class="form__item-input">
                    <input type="text" name="name" value="{{ old('name') }}" class="form__input">
                </div>
                <div class="form__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
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
                    <input type="password" name="password" value="{{ old('password') }}" class="form__input">
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__item">
                <div class="form__item-name">
                    <p class="form__label">
                        パスワード確認
                    </p>
                </div>
                <div class="form__item-input">
                    <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" class="form__input">
                </div>
                <div class="form__error">
                    @error('password_confirmation')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">登録する</button>
            </div>
        </form>
    </div>
    <div class="login__link">
        <a class="login__button" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__top">
            <div class="header__ttl">
                <a href="/" class="header__logo">
                    <h1>
                        <img src="{{ asset('storage/img/logo.svg') }}" alt="COACHTECH Logo">
                    </h1>
                </a>
            </div>
        </div>

        @if (!Request::is('login') && !Request::is('register') && !Request::is('admin/login'))
        <div class="header__bottom">
            <nav>
                @if(auth()->guard('admin')->check())
                <div class="nav__button">
                    <a href="/admin/attendance/list" class="button">
                        勤怠一覧
                    </a>
                </div>
                <div class="nav__button">
                    <a href="/admin/staff/list" class="button">
                        スタッフ一覧
                    </a>
                </div>
                <div class="nav__button">
                    <a href="/stamp_correction_request/list" class="button">
                        申請一覧
                    </a>
                </div>
                <div class="nav__button">
                    <form action="/admin/logout" class="nav__logout" method="post">
                        @csrf
                        <button type="submit" class="logout__button button">ログアウト</button>
                    </form>
                </div>
                @elseif(auth()->check())
                <div class="nav__button">
                    <a href="/" class="button">
                        勤怠
                    </a>
                </div>
                <div class="nav__button">
                    <a href="/attendance/list" class="button">
                        勤怠一覧
                    </a>
                </div>
                <div class="nav__button">
                    <a href="/stamp_correction_request/list" class="button">
                        申請
                    </a>
                </div>
                <div class="nav__button">
                    <form action="/logout" class="nav__logout" method="post">
                        @csrf
                        <button type="submit" class="logout__button button">ログアウト</button>
                    </form>
                </div>
                @endif
            </nav>
        </div>
        @endif
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>
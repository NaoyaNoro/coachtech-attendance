<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;

use Laravel\Fortify\Contracts\LoginResponse;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Event::listen(Registered::class, function ($event) {
            session(['url.intended' => route('verification.notice')]);
        });

        Fortify::registerView(function (){
            return view('auth.register');
        });

        Fortify::loginView(function(){
            if (request()->is('admin/login')){
                return view('auth.admin-login');
            }
            return view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request){
            $credentials=$request->only('email','password');
            if($request->is('admin/login')){
                if(Auth::guard('admin')->attempt($credentials)){
                    return Auth::guard('admin')->user();
                }
            }

            // if ($request->is('login')) {
            //     if (Auth::guard('web')->attempt($credentials)) {
            //         $user = Auth::guard('web')->user();

            //         if (!$user instanceof MustVerifyEmail || $user->hasVerifiedEmail()) {
            //             return $user;
            //         }

            //         // ログイン拒否（未認証）
            //         Auth::guard('web')->logout();
            //         return null;
            //     }
            // }

            if ($request->is('login')) {
                if (Auth::guard('web')->attempt($credentials)) {
                    $user = Auth::guard('web')->user();

                    if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
                        // ログインは許可するが、認証済みでないので /email/verify にリダイレクトさせる
                        $user->sendEmailVerificationNotification();
                        return $user;
                    }

                    return $user;
                }
            }

            return null;
        });

        RateLimiter::for('login',function(Request $request){
            $email=(string)$request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);


        $this->app->bind(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    if (
                        $request->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&
                        !$request->user()->hasVerifiedEmail()
                    ) {
                        return redirect()->route('verification.notice');
                    }

                    return redirect()->intended(Fortify::redirects('login'));
                }
            };
        });
    }
}

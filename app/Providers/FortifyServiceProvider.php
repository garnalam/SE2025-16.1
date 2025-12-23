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
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

// --- THÊM 2 DÒNG NÀY ---
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

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
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // --- BẮT ĐẦU CODE THÊM MỚI ĐỂ PHÂN LUỒNG ---

        /**
         * Tùy chỉnh chuyển hướng SAU KHI ĐĂNG NHẬP (LOGIN)
         */
        $this->app->singleton(LoginResponse::class, function ($app) {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $role = auth()->user()->role;

                    if ($role === 'teacher') {
                        // Giáo viên: Về dashboard mặc định (có team)
                        return redirect()->intended(config('fortify.home')); // Thường là /dashboard
                    }

                    if ($role === 'student') {
                        // === SỬA LỖI ===
                        // Học sinh: Về dashboard (web.php sẽ tự điều hướng đúng)
                        return redirect()->intended(route('dashboard'));
                    }

                    // Fallback (dự phòng)
                    return redirect()->intended(config('fortify.home'));
                }
            };
        });

        /**
         * Tùy chỉnh chuyển hướng SAU KHI ĐĂNG KÝ (REGISTER)
         */
        $this->app->singleton(RegisterResponse::class, function ($app) {
            return new class implements RegisterResponse {
                public function toResponse($request)
                {
                    $role = auth()->user()->role; // Lấy role của user vừa tạo

                    if ($role === 'teacher') {
                        // Giáo viên: Về dashboard mặc định
                        return redirect(config('fortify.home'));
                    }

                    if ($role === 'student') {
                        // === SỬA LỖI ===
                        // Học sinh: Về dashboard (web.php sẽ tự điều hướng đúng)
                        // (config('fortify.home') thường là 'dashboard')
                        return redirect(config('fortify.home'));
                    }

                    // Fallback (dự phòng)
                    return redirect(config('fortify.home'));
                }
            };
        });
        
        // --- KẾT THÚC CODE THÊM MỚI ---
    }
}


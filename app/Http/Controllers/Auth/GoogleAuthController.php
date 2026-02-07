<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', ['error' => $e->getMessage()]);
            return redirect()->route('home')->with('error', 'Đăng nhập Google thất bại. Vui lòng thử lại.');
        }

        $googleId = (string) ($googleUser->getId() ?? '');
        $email = strtolower((string) ($googleUser->getEmail() ?? ''));

        if ($googleId === '' || $email === '') {
            return redirect()->route('home')->with('error', 'Không lấy được thông tin email từ Google.');
        }

        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (!$user) {
            $defaultCredits = (float) config('services_custom.pricing.default_credits', 10);
            try {
                $defaultCredits = (float) (Setting::get('default_credits', $defaultCredits) ?: $defaultCredits);
            } catch (\Throwable $e) {
                Log::warning('Google OAuth: failed to load default credits from settings', [
                    'error' => $e->getMessage(),
                ]);
            }

            $user = User::create([
                'name' => (string) ($googleUser->getName() ?: Str::before($email, '@')),
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(40)),
                'credits' => $defaultCredits,
            ]);
        } else {
            $user->name = (string) ($googleUser->getName() ?: $user->name);
            $user->email = $email;
            $user->google_id = $googleId;
            $user->avatar = (string) ($googleUser->getAvatar() ?: $user->avatar);
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            if (empty($user->password)) {
                $user->password = Hash::make(Str::random(40));
            }
            $user->save();
        }

        if (!$user->is_active) {
            return redirect()->route('home')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ hỗ trợ.');
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}


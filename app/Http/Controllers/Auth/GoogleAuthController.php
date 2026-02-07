<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (!$this->hasGoogleOAuthConfig()) {
            Log::error('Google OAuth redirect blocked: missing GOOGLE_* configuration', [
                'has_client_id' => !empty(config('services.google.client_id')),
                'has_client_secret' => !empty(config('services.google.client_secret')),
                'redirect_uri' => config('services.google.redirect'),
            ]);

            return redirect()
                ->route('home')
                ->with('error', 'Chưa cấu hình đăng nhập Google trên hệ thống.')
                ->with('open_auth_modal', true);
        }

        try {
            return Socialite::driver('google')
                ->with(['prompt' => 'select_account'])
                ->redirect();
        } catch (\Throwable $e) {
            Log::error('Google OAuth redirect failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', 'Không thể kết nối Google Login. Vui lòng thử lại.')
                ->with('open_auth_modal', true);
        }
    }

    public function callback(): RedirectResponse
    {
        if (!$this->hasGoogleOAuthConfig()) {
            return redirect()
                ->route('home')
                ->with('error', 'Chưa cấu hình đăng nhập Google trên hệ thống.')
                ->with('open_auth_modal', true);
        }

        if (!$this->hasGoogleUserColumns()) {
            Log::error('Google OAuth callback blocked: users table missing required columns', [
                'missing_columns' => $this->missingGoogleUserColumns(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', 'Database chưa cập nhật cho Google Login. Vui lòng liên hệ admin.')
                ->with('open_auth_modal', true);
        }

        try {
            try {
                $googleUser = Socialite::driver('google')->user();
            } catch (InvalidStateException $e) {
                Log::warning('Google OAuth callback invalid state, fallback to stateless', [
                    'error' => $e->getMessage(),
                ]);

                $googleUser = Socialite::driver('google')->stateless()->user();
            }
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', 'Đăng nhập Google thất bại. Vui lòng thử lại.')
                ->with('open_auth_modal', true);
        }

        $googleId = (string) ($googleUser->getId() ?? '');
        $email = strtolower((string) ($googleUser->getEmail() ?? ''));

        if ($googleId === '' || $email === '') {
            return redirect()
                ->route('home')
                ->with('error', 'Không lấy được thông tin email từ Google.')
                ->with('open_auth_modal', true);
        }

        try {
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();
        } catch (\Throwable $e) {
            Log::error('Google OAuth user lookup failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', 'Không thể truy vấn tài khoản. Vui lòng thử lại.')
                ->with('open_auth_modal', true);
        }

        try {
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
        } catch (\Throwable $e) {
            Log::error('Google OAuth user create/update failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', 'Không thể tạo/cập nhật tài khoản. Vui lòng thử lại.')
                ->with('open_auth_modal', true);
        }

        if (!$user->is_active) {
            return redirect()
                ->route('home')
                ->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ hỗ trợ.')
                ->with('open_auth_modal', true);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    private function hasGoogleOAuthConfig(): bool
    {
        return !empty(config('services.google.client_id'))
            && !empty(config('services.google.client_secret'))
            && !empty(config('services.google.redirect'));
    }

    private function hasGoogleUserColumns(): bool
    {
        return count($this->missingGoogleUserColumns()) === 0;
    }

    /**
     * @return array<int, string>
     */
    private function missingGoogleUserColumns(): array
    {
        $requiredColumns = ['google_id', 'avatar'];

        return array_values(array_filter(
            $requiredColumns,
            static fn (string $column): bool => !Schema::hasColumn('users', $column)
        ));
    }
}

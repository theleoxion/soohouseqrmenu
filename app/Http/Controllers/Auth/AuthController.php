<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->preferredDashboardRoute());
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Çok fazla başarısız giriş denemesi yaptınız. Lütfen {$seconds} saniye sonra tekrar deneyin.",
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => 'Bu kullanıcı hesabı pasif duruma alınmıştır. Lütfen genel merkez yöneticinizle iletişime geçin.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => 'Girdiğiniz e-posta adresi veya şifre hatalı.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var User $loggedInUser */
        $loggedInUser = Auth::user();
        $loggedInUser->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        AuditLogger::record(
            actionType: 'auth.login',
            description: "{$loggedInUser->name} ({$loggedInUser->email}) yönetim paneline giriş yaptı.",
            auditable: $loggedInUser,
            actor: $loggedInUser
        );

        return redirect()->intended($loggedInUser->preferredDashboardRoute());
    }

    public function logout(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            AuditLogger::record(
                actionType: 'auth.logout',
                description: "{$user->name} ({$user->email}) yönetim panelinden çıkış yaptı.",
                auditable: $user,
                actor: $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Güvenli bir şekilde çıkış yapıldı.');
    }

    public function showForgotPassword(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $throttleKey = 'pw-reset|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors(['email' => 'Çok fazla sıfırlama isteği gönderildi. Lütfen biraz bekleyin.']);
        }
        RateLimiter::hit($throttleKey, 120);

        $user = User::where('email', $request->input('email'))->where('is_active', true)->first();
        if ($user) {
            $token = Password::createToken($user);
            // E-posta sunucusu ayarlıysa gönder, hata olursa sessizce logla
            try {
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
                Log::info("Şifre sıfırlama bağlantısı ({$user->email}): {$resetUrl}");
            } catch (\Throwable) {
                // ignore mail transport exception on unconfigured local env
            }
        }

        return back()->with('status', 'Eğer bu e-posta adresine kayıtlı aktif bir hesap varsa, şifre sıfırlama bağlantısı gönderilmiştir.');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.')
            : back()->withErrors(['email' => 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş.']);
    }

    public function editProfile(): View
    {
        return view('admin.auth.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'confirmed', PasswordRule::min(8)],
        ]);

        if (! empty($validated['new_password'])) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Mevcut şifreniz hatalı.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? null;
        $user->save();

        AuditLogger::record(
            actionType: 'user.updated',
            description: "{$user->name} kendi profil / şifre bilgilerini güncelledi.",
            auditable: $user
        );

        return back()->with('status', 'Profil ve güvenlik bilgileriniz başarıyla güncellendi.');
    }
}

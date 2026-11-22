<?php

namespace App\Http\Controllers;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect(home_for_role(Auth::user()->role));
        }

        $email = '';
        $next = safe_next($request->query('next') ?? $request->old('next'), '');

        if ($request->has('logged_out')) {
            flash('info', 'You have been logged out.');
        }

        return view('auth.login', [
            'errors' => [],
            'email' => $email,
            'next' => $next,
            'pageTitle' => 'Log In',
            'minimalHeader' => true,
        ]);
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect(home_for_role(Auth::user()->role));
        }

        $errors = [];
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');
        $remember = $request->has('remember');
        $next = safe_next($request->input('next'), '');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if ($password === '') {
            $errors['password'] = 'Enter your password.';
        }

        if (! $errors) {
            if (login_is_locked($email)) {
                $errors['form'] = 'Too many failed login attempts. Please wait ' . config('booking.login_lock_minutes') . ' minutes and try again.';
            } else {
                $user = User::where('email', $email)->first();

                if ($user && $user->status === 'active' && password_verify($password, $user->password_hash)) {
                    clear_login_attempts($email);
                    Auth::login($user, $remember);

                    flash('success', 'Welcome back, ' . $user->first_name . '!');

                    return redirect($next !== '' ? $next : home_for_role($user->role));
                }

                if ($user && $user->status === 'pending' && password_verify($password, $user->password_hash)) {
                    $errors['form'] = 'Your account is still waiting for admin approval. Please try again later.';
                } elseif ($user && $user->status === 'deactivated' && password_verify($password, $user->password_hash)) {
                    $errors['form'] = 'This account has been deactivated. Please contact the resort administrator.';
                } else {
                    record_failed_login($email);
                    $errors['form'] = 'Invalid credentials, or your account is not active yet.';
                }
            }
        }

        return view('auth.login', [
            'errors' => $errors,
            'email' => $email,
            'next' => $next,
            'pageTitle' => 'Log In',
            'minimalHeader' => true,
        ]);
    }

    public function showSignup(Request $request)
    {
        if (Auth::check()) {
            return redirect(home_for_role(Auth::user()->role));
        }

        return view('auth.signup', [
            'errors' => [],
            'values' => [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'role' => 'guest',
            ],
            'pageTitle' => 'Sign Up',
            'minimalHeader' => true,
        ]);
    }

    public function signup(Request $request)
    {
        if (Auth::check()) {
            return redirect(home_for_role(Auth::user()->role));
        }

        $errors = [];
        $values = [
            'first_name' => trim((string) $request->input('first_name', '')),
            'last_name' => trim((string) $request->input('last_name', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phone' => trim((string) $request->input('phone', '')),
            'role' => (string) $request->input('role', 'guest'),
        ];
        $password = (string) $request->input('password', '');
        $confirm = (string) $request->input('confirm_password', '');
        $consent = $request->has('consent');

        $phoneDigits = preg_replace('/\D+/', '', $values['phone']) ?? '';

        if ($values['first_name'] === '' || mb_strlen($values['first_name']) > 60) {
            $errors['first_name'] = 'Enter your first name (60 characters or fewer).';
        }
        if ($values['last_name'] === '' || mb_strlen($values['last_name']) > 60) {
            $errors['last_name'] = 'Enter your last name (60 characters or fewer).';
        }
        if (! filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if (! preg_match('/^09\d{9}$/', $phoneDigits)) {
            $errors['phone'] = 'Use the format 09XX XXX XXXX.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Use at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors['confirm_password'] = 'The passwords do not match.';
        }
        if (! in_array($values['role'], ['guest', 'owner'], true)) {
            $errors['form'] = 'Choose a valid account type.';
        }
        if (! $consent) {
            $errors['consent'] = 'You must agree to the Terms of Service and Privacy Policy.';
        }

        if (! $errors && User::where('email', $values['email'])->exists()) {
            $errors['email'] = 'An account with that email already exists.';
        }

        if (! $errors) {
            $status = $values['role'] === 'owner' ? 'pending' : 'active';

            $user = User::create([
                'first_name' => $values['first_name'],
                'last_name' => $values['last_name'],
                'email' => $values['email'],
                'phone' => $phoneDigits,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $values['role'],
                'status' => $status,
            ]);

            if ($values['role'] === 'guest') {
                Auth::login($user);
                flash('success', 'Your guest account is ready. Start by choosing a cottage.');

                return redirect('/');
            }

            flash('success', 'Your owner account was created and is waiting for admin approval.');

            return redirect()->route('login');
        }

        return view('auth.signup', [
            'errors' => $errors,
            'values' => $values,
            'pageTitle' => 'Sign Up',
            'minimalHeader' => true,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login', ['logged_out' => 1]);
    }

    public function showForgot(Request $request)
    {
        if (Auth::check()) {
            return redirect(home_for_role(Auth::user()->role));
        }

        return view('auth.forgot-password', [
            'errors' => [],
            'done' => false,
            'email' => '',
            'pageTitle' => 'Forgot Password',
            'minimalHeader' => true,
        ]);
    }

    public function sendReset(Request $request)
    {
        if (Auth::check()) {
            return redirect(home_for_role(Auth::user()->role));
        }

        $errors = [];
        $done = false;
        $email = strtolower(trim((string) $request->input('email', '')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if (! $errors) {
            $user = User::where('email', $email)->where('status', 'active')->first();

            if ($user) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                PasswordReset::where('user_id', $user->id)->where('used', false)->update(['used' => true]);

                $reset = PasswordReset::create([
                    'user_id' => $user->id,
                    'token_hash' => password_hash($code, PASSWORD_DEFAULT),
                    'expires_at' => now()->addMinutes((int) config('booking.reset_code_expiry_minutes')),
                    'attempts' => 0,
                    'verified_at' => null,
                ]);

                $body = "Hello " . $user->first_name . ",\n\n"
                    . "We received a request to reset your OliyetteBook password.\n"
                    . "Enter this reset code within "
                    . (int) config('booking.reset_code_expiry_minutes')
                    . " minutes:\n\n"
                    . $code . "\n\n"
                    . "If you did not request this, you can ignore this email.\n\n"
                    . "— OliyetteBook";

                if (! send_mail($user->email, 'Reset your OliyetteBook password', $body)) {
                    $reset->update(['used' => true]);
                }
            }

            $done = true;
        }

        return view('auth.forgot-password', [
            'errors' => $errors,
            'done' => $done,
            'email' => $email,
            'pageTitle' => 'Forgot Password',
            'minimalHeader' => true,
        ]);
    }

    public function showReset(Request $request)
    {
        if (Auth::check()) {
            return redirect(home_for_role(Auth::user()->role));
        }

        $errors = [];
        $showPasswordForm = false;
        $email = strtolower(trim((string) $request->input('email', '')));
        $resetId = (int) $request->session()->get('password_reset_id', 0);
        $reset = $resetId > 0
            ? PasswordReset::where('id', $resetId)
                ->where('used', false)
                ->whereNotNull('verified_at')
                ->where('expires_at', '>', now())
                ->first()
            : null;

        if ($reset) {
            $showPasswordForm = true;
        } elseif ($resetId > 0) {
            $request->session()->forget('password_reset_id');
            $errors['form'] = 'This reset code is invalid or has expired. Request a new code.';
        }

        if ($request->isMethod('post') && $showPasswordForm) {
            $password = (string) $request->input('password', '');
            $confirm = (string) $request->input('confirm_password', '');

            if (strlen($password) < 8) {
                $errors['password'] = 'Use at least 8 characters.';
            }
            if ($password !== $confirm) {
                $errors['confirm_password'] = 'The passwords do not match.';
            }

            if (! $errors) {
                try {
                    DB::transaction(function () use ($password, $resetId) {
                        $verifiedReset = PasswordReset::where('id', $resetId)
                            ->where('used', false)
                            ->whereNotNull('verified_at')
                            ->lockForUpdate()
                            ->first();

                        if (! $verifiedReset || $verifiedReset->expires_at->isPast()) {
                            throw new \RuntimeException('The verified reset code has expired.');
                        }

                        User::where('id', $verifiedReset->user_id)->update([
                            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        ]);
                        PasswordReset::where('user_id', $verifiedReset->user_id)->update(['used' => true]);
                    });

                    $request->session()->forget('password_reset_id');
                    flash('success', 'Password updated successfully.');

                    return redirect()->route('login');
                } catch (\Throwable $e) {
                    report($e);
                    $errors['form'] = 'We could not update your password. Request a new code and try again.';
                    $request->session()->forget('password_reset_id');
                    $showPasswordForm = false;
                }
            }
        } elseif ($request->isMethod('post')) {
            $email = strtolower(trim((string) $request->input('email', '')));
            $code = trim((string) $request->input('code', ''));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! preg_match('/^\d{6}$/', $code)) {
                $errors['form'] = 'Invalid or expired reset code.';
            } else {
                $result = DB::transaction(function () use ($email, $code) {
                    $user = User::where('email', $email)->where('status', 'active')->first();

                    if (! $user) {
                        return ['state' => 'invalid'];
                    }

                    $reset = PasswordReset::where('user_id', $user->id)
                        ->where('used', false)
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();

                    if (! $reset || $reset->expires_at->isPast()) {
                        return ['state' => 'invalid'];
                    }
                    if ($reset->attempts >= (int) config('booking.reset_code_max_attempts')) {
                        $reset->update(['used' => true]);

                        return ['state' => 'locked'];
                    }
                    if (! password_verify($code, $reset->token_hash)) {
                        $attempts = $reset->attempts + 1;
                        $reset->update([
                            'attempts' => $attempts,
                            'used' => $attempts >= (int) config('booking.reset_code_max_attempts'),
                        ]);

                        return [
                            'state' => $attempts >= (int) config('booking.reset_code_max_attempts')
                                ? 'locked'
                                : 'invalid',
                        ];
                    }

                    $reset->update(['verified_at' => now()]);

                    return ['state' => 'verified', 'id' => (int) $reset->id];
                });

                if ($result['state'] === 'verified') {
                    $request->session()->put('password_reset_id', $result['id']);
                    flash('success', 'Code verified. Choose a new password.');

                    return redirect('/reset-password');
                }

                $errors['form'] = $result['state'] === 'locked'
                    ? 'Too many attempts. Start again with a new reset code.'
                    : 'Invalid or expired reset code.';
            }
        }

        return view('auth.reset-password', [
            'errors' => $errors,
            'email' => $email,
            'showPasswordForm' => $showPasswordForm,
            'pageTitle' => 'Reset Password',
            'minimalHeader' => true,
        ]);
    }
}

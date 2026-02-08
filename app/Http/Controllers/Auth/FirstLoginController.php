<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OneTimePasswordMail;
use App\Models\Employee;
use App\Models\LoginOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FirstLoginController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.first-login-email');
    }

    public function sendOtp(Request $request)
    {
        $rules = [
            'email' => ['required', 'email'],
            'sent_at' => ['nullable', 'integer'],
            'sent_sig' => ['nullable', 'string'],
        ];

        if (!$request->filled('sent_at')) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        if (array_key_exists('g-recaptcha-response', $validated)) {
            if (!$this->verifyRecaptcha($validated['g-recaptcha-response'], $request->ip())) {
                return back()->withErrors([
                    'g-recaptcha-response' => 'Captcha verification failed. Please try again.',
                ])->withInput($request->only('email'));
            }
        }

        $employee = Employee::where('email', $validated['email'])->first();
        if (!$employee) {
            return back()->withErrors(['email' => 'Email does not match any employee profile.']);
        }

        $user = User::firstOrCreate(
            ['email' => $employee->email],
            [
                'name' => $employee->getFullName(),
                'password' => Hash::make(Str::random(32)),
                'password_set_at' => null,
            ]
        );

        if ($user->password_set_at) {
            return redirect()->route('login')->with('status', 'Your password is already set. Please log in.');
        }

        $latestOtp = LoginOtp::where('email', $employee->email)
            ->orderByDesc('id')
            ->first();

        $lastSentAt = $latestOtp?->created_at?->timestamp;

        if (!empty($validated['sent_at']) && !empty($validated['sent_sig'])) {
            $signedTs = $this->verifySignedTimestamp((string) $validated['sent_at'], $validated['sent_sig']);
            if ($signedTs) {
                $lastSentAt = max($lastSentAt ?? 0, (int) $signedTs);
            }
        }

        if ($lastSentAt && $lastSentAt > now()->subMinutes(3)->timestamp) {
            return back()->withErrors(['email' => 'Please wait 3 minutes before requesting a new OTP.']);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            LoginOtp::create([
            'email' => $employee->email,
            'code_hash' => Hash::make($otp),
                'code_encrypted' => Crypt::encryptString($otp),
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($employee->email)->send(new OneTimePasswordMail($otp));

        $request->session()->put('first_login_email', $employee->email);
        $request->session()->put('first_login_otp_sent_at', now()->timestamp);
        $request->session()->put('first_login_otp_sent_sig', $this->signTimestamp((string) now()->timestamp));

        return redirect()->route('first-login.verify')->with('status', 'OTP sent to your email.');
    }

    public function showVerifyForm(Request $request)
    {
        if (!$request->session()->has('first_login_email')) {
            return redirect()->route('first-login.email');
        }

        return view('auth.first-login-verify', [
            'sentAt' => $request->session()->get('first_login_otp_sent_at'),
            'sentSig' => $request->session()->get('first_login_otp_sent_sig'),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->session()->get('first_login_email');
        if (!$email) {
            return redirect()->route('first-login.email');
        }

        $otpRecord = LoginOtp::where('email', $email)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        if (!$otpRecord || $otpRecord->expires_at->lt(now())) {
            return back()->withErrors(['otp' => 'OTP expired. Please request a new one.']);
        }

        if ($otpRecord->attempts >= 5) {
            return back()->withErrors(['otp' => 'OTP attempt limit reached. Please request a new one.']);
        }

        if (!Hash::check($validated['otp'], $otpRecord->code_hash)) {
            $otpRecord->increment('attempts');
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        $otpRecord->update(['used_at' => now()]);

        $request->session()->put('first_login_verified', true);

        return redirect()->route('first-login.password');
    }

    public function showPasswordForm(Request $request)
    {
        if (!$request->session()->get('first_login_verified')) {
            return redirect()->route('first-login.email');
        }

        return view('auth.first-login-password');
    }

    public function setPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = $request->session()->get('first_login_email');
        if (!$email) {
            return redirect()->route('first-login.email');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('first-login.email');
        }

        $user->password = Hash::make($validated['password']);
        $user->password_set_at = now();
        $user->save();

        Auth::login($user);

        $request->session()->forget(['first_login_email', 'first_login_verified']);

        return redirect()->route('dashboard');
    }

    private function signTimestamp(string $timestamp): string
    {
        return hash_hmac('sha256', $timestamp, config('app.key'));
    }

    private function verifySignedTimestamp(string $timestamp, string $signature): ?string
    {
        $expected = $this->signTimestamp($timestamp);

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        return $timestamp;
    }

    private function verifyRecaptcha(?string $token, ?string $ip = null): bool
    {
        $secret = config('services.recaptcha.secret_key');

        if (!$secret || !$token) {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->ok() && data_get($response->json(), 'success') === true;
    }
}

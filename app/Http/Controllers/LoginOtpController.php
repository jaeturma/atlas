<?php

namespace App\Http\Controllers;

use App\Models\LoginOtp;
use App\Models\User;

class LoginOtpController extends Controller
{
    public function index()
    {
        $otps = LoginOtp::orderByDesc('created_at')->paginate(50);

        $users = User::whereIn('email', $otps->pluck('email')->filter()->unique())
            ->get()
            ->keyBy('email');

        return view('login-otps.index', compact('otps', 'users'));
    }
}

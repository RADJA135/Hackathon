<?php

namespace App\Http\Controllers;

use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:8',
        ]);

        // One new row per login attempt — the 3 signal controllers and the
        // Decision Agent will all fill in more columns on this same row.
        $check = TrustCheck::create([
            'phone_number' => $request->phone,
            'user_id' => Auth::id(), // null is fine for the hackathon demo
        ]);

        session(['trust_check_id' => $check->id]);

        return redirect()->route('scan');
    }

    public function showScan()
    {
        $trustCheckId = session('trust_check_id');

        if (! $trustCheckId) {
            return redirect()->route('root');
        }

        return Inertia::render('Scan', [
            'trustCheckId' => $trustCheckId,
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('trust_check_id');
        $request->session()->regenerate();

        return redirect()->route('root');
    }
}

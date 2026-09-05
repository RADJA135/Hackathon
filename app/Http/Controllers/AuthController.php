<?php

namespace App\Http\Controllers;

use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            'user_id' => auth()->id(), // null is fine for the hackathon demo
        ]);

        session(['trust_check_id' => $check->id]);

        return redirect()->route('scan');
    }

    public function showScan()
    {
        $trustCheckId = session('trust_check_id');

        if (! $trustCheckId) {
            return redirect()->route('login');
        }

        return Inertia::render('Scan', [
            'trustCheckId' => $trustCheckId,
        ]);
    }
}

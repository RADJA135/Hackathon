<?php

namespace App\Http\Controllers;

use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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


        $check = TrustCheck::create([
            'phone_number' => $request->phone,
            'user_id' => Auth::id(), 
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

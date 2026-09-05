<?php

namespace App\Http\Controllers\Auth;

use App\Models\TrustCheck;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PhoneLoginController
{
    /**
     * Display the login form.
     */
    public function showLoginForm(): Response
    {
        return Inertia::render('auth/Login');
    }

    /**
     * Handle a phone-number login request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $phoneNumber = $this->normalizePhoneNumber($request->input('phone_number'));

        $user = User::firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'name' => $phoneNumber,
                'email' => null,
                'password' => null,
            ]
        );

        if (! $this->checkTrustScore($phoneNumber)) {
            return back()->withErrors([
                'phone_number' => 'Your trust score does not currently allow access.',
            ])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $trustCheck = TrustCheck::create([
            'phone_number' => $phoneNumber,
            'user_id' => $user->id,
        ]);

        $request->session()->put('trust_check_id', $trustCheck->id);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Normalize the phone number to E.164 format.
     */
    protected function normalizePhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/\s+/', '', trim($phoneNumber));

        if (str_starts_with($phoneNumber, '+')) {
            return $phoneNumber;
        }

        return '+'.$phoneNumber;
    }

    /**
     * Extension point for future AI trust scoring before Auth::login().
     *
     * This currently always allows access and can later call a TrustAI service
     * to evaluate SIM swap, device status, location, and other signals.
     */
    protected function checkTrustScore(string $phoneNumber): bool
    {
        // TODO: replace with real TrustAI evaluation.
        // Example: $decision = app(TrustScoreService::class)->evaluate($phoneNumber);
        // return $decision->allowsAccess();

        return true;
    }
}

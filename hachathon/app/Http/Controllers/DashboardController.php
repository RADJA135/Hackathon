<?php

namespace App\Http\Controllers;

use App\Models\TrustCheck;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $trustCheckId = session('trust_check_id');

        if (! $trustCheckId) {
            return Redirect::route('login');
        }

        $check = TrustCheck::findOrFail($trustCheckId);

        return Inertia::render('Dashboard', [
            'trustCheck' => $check,
        ]);
    }
}

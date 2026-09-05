<?php

namespace App\Http\Controllers;

use App\Models\TrustCheck;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $trustCheckId = session('trust_check_id');
        $check = $trustCheckId ? TrustCheck::find($trustCheckId) : null;

        return Inertia::render('Dashboard', [
            'trustCheck' => $check,
        ]);
    }
}

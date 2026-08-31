<?php

namespace App\Http\Controllers;

use App\Models\TrustCheck;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function index()
    {
        $query = TrustCheck::query()->latest();

        // Once real auth is wired up, scope to the logged-in user:
        // $query->where('user_id', auth()->id());
        // For the hackathon demo, show the most recent attempts across all sessions:
        $checks = $query->take(20)->get();

        return Inertia::render('History', [
            'checks' => $checks,
        ]);
    }
}

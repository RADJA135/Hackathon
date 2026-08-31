<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DecisionController extends Controller
{
    public function decide(Request $request)
    {
        $check = TrustCheck::findOrFail($request->trust_check_id);

        // Call the Python AI agent service (runs separately, e.g. localhost:8001)
        $response = Http::timeout(30)->post(env('AI_AGENT_URL', 'http://localhost:8001').'/decide', [
            'phone_number' => $check->phone_number,
            'sim_swapped' => $check->sim_swapped,
            'sim_swap_last_changed' => $check->sim_swap_last_changed,
            'device_known' => $check->device_known,
            'location_consistent' => $check->location_consistent,
            'location_country' => $check->location_country,
        ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'AI agent unavailable'], 502);
        }

        $result = $response->json();

        $check->update([
            'trust_score' => $result['trust_score'],
            'decision' => $result['decision'],
            'agent_reasoning' => $result['reasoning'],
        ]);

        return response()->json($check);
    }
}

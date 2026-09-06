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
        // Allow long-running Python request (prevents PHP timeout)
        set_time_limit(120);

        $check = TrustCheck::findOrFail($request->trust_check_id);

        $signals = [
            'sim_swapped' => (bool) $check->sim_swapped,
            'device_known' => (bool) $check->device_known,
            'location_consistent' => $check->location_consistent === null ? true : (bool) $check->location_consistent,
        ];

        $response = Http::timeout(120)->post(env('AI_AGENT_URL', 'http://localhost:8001').'/decide', [
            'trust_check_id' => $check->id,
            'signals' => $signals,
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

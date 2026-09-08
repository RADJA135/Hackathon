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
        set_time_limit(120);
        $check = TrustCheck::findOrFail($request->trust_check_id);

        // ---------- DEMO OVERRIDE (remove after presentation) ----------
        $demoNumbers = [
            '+99999991000', // → 50 (SIM True, Device True, Location True)
            '+99999991001', // → 80 (SIM False, Device True, Location False)
            '+99999991002', // → 70 (SIM False, Device False, Location True)
            '+99999991003', // → 100 (SIM False, Device True, Location True)
            '+99999991004', // → 0  (SIM True, Device False, Location False)
        ];

        if (in_array($check->phone_number, $demoNumbers)) {
            // Manually set signals based on the number
            switch ($check->phone_number) {
                case '+99999991000':
                    $signals = ['sim_swapped' => true,  'device_known' => true, 'location_consistent' => true];
                    break;
                case '+99999991001':
                    $signals = ['sim_swapped' => false, 'device_known' => true, 'location_consistent' => false];
                    break;
                case '+99999991002':
                    $signals = ['sim_swapped' => false, 'device_known' => false, 'location_consistent' => true];
                    break;
                case '+99999991003':
                    $signals = ['sim_swapped' => false, 'device_known' => true, 'location_consistent' => true];
                    break;
                case '+99999991004':
                    $signals = ['sim_swapped' => true,  'device_known' => false, 'location_consistent' => false];
                    break;
            }

            // 🔥 CRITICAL FIX – save the override values to the database
            // This makes the badges match the reasoning message.
            $check->sim_swapped = $signals['sim_swapped'];
            $check->device_known = $signals['device_known'];
            $check->location_consistent = $signals['location_consistent'];
            $check->save();
        } else {
            // Normal flow – read from database
            $signals = [
                'sim_swapped' => (bool) $check->sim_swapped,
                'device_known' => (bool) $check->device_known,
                'location_consistent' => $check->location_consistent === null ? true : (bool) $check->location_consistent,
            ];
        }
        // ----------------------------------------------------------------

        $response = Http::timeout(120)->post(env('AI_AGENT_URL', 'http://localhost:8001').'/decide', [
            'trust_check_id' => $check->id,
            'signals' => $signals,
        ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'AI agent unavailable'], 502);
        }

        $result = $response->json();

        // Note: the database values are already updated above,
        // but we still need to save the score/decision/reasoning.
        $check->update([
            'trust_score' => $result['trust_score'],
            'decision' => $result['decision'],
            'agent_reasoning' => $result['reasoning'],
        ]);

        return response()->json($check);
    }
}
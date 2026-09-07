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

        // -----------------------------------------------------------------
        // DEMO OVERRIDE — remove or comment out after the presentation
        // These phone numbers are for demonstration purposes only.
        // They return predefined signal combinations to show different scores.
        // -----------------------------------------------------------------
        $demoNumbers = [
            '+99999991000', // SIM True,  Device True,  Location True  → 50  (WARN)
            '+99999991001', // SIM False, Device True,  Location False → 80  (ALLOW)
            '+99999991002', // SIM False, Device False, Location True  → 70  (ALLOW)
            '+99999991003', // SIM False, Device True,  Location True  → 100 (ALLOW)
            '+99999991004', // SIM True,  Device False, Location False → 0   (BLOCK)
        ];

        if (in_array($check->phone_number, $demoNumbers)) {
            // Manually set the signals based on the phone number
            switch ($check->phone_number) {
                case '+99999991000':
                    $signals = [
                        'sim_swapped'        => true,
                        'device_known'       => true,
                        'location_consistent'=> true,
                    ];
                    break;
                case '+99999991001':
                    $signals = [
                        'sim_swapped'        => false,
                        'device_known'       => true,
                        'location_consistent'=> false,
                    ];
                    break;
                case '+99999991002':
                    $signals = [
                        'sim_swapped'        => false,
                        'device_known'       => false,
                        'location_consistent'=> true,
                    ];
                    break;
                case '+99999991003':
                    $signals = [
                        'sim_swapped'        => false,
                        'device_known'       => true,
                        'location_consistent'=> true,
                    ];
                    break;
                case '+99999991004':
                    $signals = [
                        'sim_swapped'        => true,
                        'device_known'       => false,
                        'location_consistent'=> false,
                    ];
                    break;
                default:
                    // Fallback (should never happen)
                    $signals = [
                        'sim_swapped'        => (bool) $check->sim_swapped,
                        'device_known'       => (bool) $check->device_known,
                        'location_consistent'=> $check->location_consistent === null ? true : (bool) $check->location_consistent,
                    ];
            }
        } else {
            // Normal flow – read signals from the database
            $signals = [
                'sim_swapped'        => (bool) $check->sim_swapped,
                'device_known'       => (bool) $check->device_known,
                'location_consistent'=> $check->location_consistent === null ? true : (bool) $check->location_consistent,
            ];
        }
        // -----------------------------------------------------------------

        // Call the Python AI agent
        $response = Http::timeout(120)->post(env('AI_AGENT_URL', 'http://localhost:8001').'/decide', [
            'trust_check_id' => $check->id,
            'signals'        => $signals,
        ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'AI agent unavailable'], 502);
        }

        $result = $response->json();

        // Save the result to the database
        $check->update([
            'trust_score'      => $result['trust_score'],
            'decision'         => $result['decision'],
            'agent_reasoning'  => $result['reasoning'],
        ]);

        return response()->json($check);
    }
}
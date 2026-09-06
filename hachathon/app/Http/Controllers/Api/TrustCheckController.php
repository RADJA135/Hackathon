<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;

class TrustCheckController extends Controller
{
    // Read-only endpoint the Python Data Agent calls as a tool.
    public function show($id)
    {
        $check = TrustCheck::findOrFail($id);

        return response()->json([
            'phone_number' => $check->phone_number,
            'sim_swapped' => $check->sim_swapped,
            'sim_swap_last_changed' => $check->sim_swap_last_changed,
            'device_known' => $check->device_known,
            'location_consistent' => $check->location_consistent,
            'location_country' => $check->location_country,
        ]);
    }
}

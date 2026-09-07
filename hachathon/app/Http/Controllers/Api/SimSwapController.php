<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SimSwapController extends Controller
{
    public function check(Request $request)
    {
        $check = TrustCheck::findOrFail($request->trust_check_id);
        $cleanPhone = preg_replace('/[\s\-]/', '', $check->phone_number);

        $response = Http::timeout(30)->connectTimeout(15)->withHeaders([
            'Content-Type' => 'application/json',
            'x-rapidapi-host' => env('NOKIA_HOST'),
            'x-rapidapi-key' => env('NOKIA_API_KEY'),
        ])->post(env('NOKIA_SIM_SWAP_URL'), [
            'phoneNumber' => $cleanPhone,
            'maxAge' => 240,
        ]);

        $data = $response->json() ?? [];

        if (! $response->successful()) {
                  \Log::warning('SIM swap failed', [
             'status' => $response->status(),
                'body' => $data,
               ]);
               return response()->json(['error' => 'SIM Swap check failed', 'details' => $data], 502);
}
        // Explicit save (bypasses any mass‑assignment issues)
        $check->sim_swapped = $data['swapped'] ?? false;
        $check->sim_swap_last_changed = $data['latestSimChange'] ?? null;
        $check->save();

        return response()->json([
            'sim_swapped' => $check->sim_swapped,
            'sim_swap_last_changed' => $check->sim_swap_last_changed,
        ]);
    }
}

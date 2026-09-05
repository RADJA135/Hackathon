<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SimSwapController extends Controller
{
    // Owner: Radja — real Nokia Network-as-Code SIM Swap API
    public function check(Request $request)
    {
        $check = TrustCheck::findOrFail($request->trust_check_id);
        $cleanPhone = preg_replace('/[\s\-]/', '', $check->phone_number);

        $response = Http::timeout(30)->connectTimeout(15)->withHeaders([
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => env('NOKIA_HOST'),
                'x-rapidapi-key' => env('NOKIA_API_KEY'),
            ])
            ->post(env('NOKIA_SIM_SWAP_URL'), [
                'phoneNumber' => $cleanPhone,
                'maxAge' => 240,
            ]);

        $data = $response->json() ?? [];

        $check->update([
            'sim_swapped' => $data['swapped'] ?? false,
            'sim_swap_last_changed' => $data['latestSimChange'] ?? null,
        ]);

        return response()->json([
            'sim_swapped' => $check->sim_swapped,
            'sim_swap_last_changed' => $check->sim_swap_last_changed,
            'raw_nokia_response' => $data, // remove this line once confirmed working
        ]);
    }
}

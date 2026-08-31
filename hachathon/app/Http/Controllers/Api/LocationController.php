<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    // Owner: Haddad
    public function check(Request $request)
    {
        $check = TrustCheck::findOrFail($request->trust_check_id);

        $response = Http::withToken(env('NOKIA_API_KEY'))
            ->post(env('NOKIA_BASE_URL').'/location-verification/v0/verify', [
                'phoneNumber' => $check->phone_number,
            ]);

        // TODO: replace with real Nokia NaC response field names once you've
        // registered and seen the actual sandbox response shape.
        $data = $response->json() ?? [];

        $check->update([
            'location_consistent' => $data['verified'] ?? true,
            'location_country' => $data['country'] ?? null,
            'location_city' => $data['city'] ?? null,
        ]);

        return response()->json([
            'location_consistent' => $check->location_consistent,
            'location_country' => $check->location_country,
            'location_city' => $check->location_city,
        ]);
    }
}

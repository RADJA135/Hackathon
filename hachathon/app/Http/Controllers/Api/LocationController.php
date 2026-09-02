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

        // MOCK — active until NOKIA_LOCATION_API_KEY is filled in below.
        if (empty(env('NOKIA_LOCATION_API_KEY'))) {
            sleep(1);
            $check->update([
                'location_consistent' => true,
                'location_country' => 'DZ',
                'location_city' => 'Algiers',
            ]);
            return response()->json([
                'location_consistent' => $check->location_consistent,
                'location_country' => $check->location_country,
                'location_city' => $check->location_city,
            ]);
        }

        // REAL Nokia call — activates automatically once the key above is set.
        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => env('NOKIA_LOCATION_HOST'),
                'x-rapidapi-key' => env('NOKIA_LOCATION_API_KEY'),
            ])
            ->post(env('NOKIA_LOCATION_URL'), [
                'phoneNumber' => $check->phone_number,
            ]);

        $data = $response->json() ?? [];

        // TODO Haddad: confirm real field names via Example Responses tab,
        // same way Radja did for SIM Swap — adjust below if different.
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
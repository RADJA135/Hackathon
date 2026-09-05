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
    $cleanPhone = preg_replace('/[\s\-]/', '', $check->phone_number);

    $response = Http::timeout(30)->connectTimeout(15)->withHeaders([
            'x-rapidapi-host' => env('NOKIA_LOCATION_HOST'),
            'x-rapidapi-key' => env('NOKIA_LOCATION_API_KEY'),
        ])
        ->post(env('NOKIA_LOCATION_URL'), [
            'device' => [
                'phoneNumber' => $cleanPhone,
            ],
            'area' => [
                'areaType' => 'CIRCLE',
                'center' => [
                    'latitude' => 36.7538,
                    'longitude' => 3.0588,
                ],
                'radius' => 50000,
            ],
        ]);

    $data = $response->json() ?? [];

    $check->update([
        'location_consistent' => ($data['verificationResult'] ?? 'FALSE') === 'TRUE',
        'location_country' => 'Algeria',
        'location_city' => 'Algiers',
    ]);

    return response()->json([
        'location_consistent' => $check->location_consistent,
        'location_country' => $check->location_country,
        'location_city' => $check->location_city,
    ]);
}
}

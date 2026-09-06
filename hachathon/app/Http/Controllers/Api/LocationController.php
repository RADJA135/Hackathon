<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PhoneBaseline;

class LocationController extends Controller
{
    // Owner: Haddad
    public function check(Request $request)
{
    $check = TrustCheck::findOrFail($request->trust_check_id);
    $cleanPhone = preg_replace('/[\s\-]/', '', $check->phone_number);
    $baseline = PhoneBaseline::where('phone_number', $cleanPhone)->first();
    if ($baseline === null) {
       $retrieveResponse = Http::retry(2, 1000)->timeout(30)->connectTimeout(15)->withHeaders([
            'x-rapidapi-host' => env('NOKIA_LOCATION_HOST'),
            'x-rapidapi-key' => env('NOKIA_LOCATION_API_KEY'),
        ])
        ->post(env('NOKIA_LOCATION_RETRIEVAL_URL'), [
            'device' => [
                'phoneNumber' => $cleanPhone,
            ],
            'maxAge' => 60,
        ]);
        $retrieveData = $retrieveResponse->json() ?? [];
        $latitude = $retrieveData['area']['center']['latitude'] ?? null;
        $longitude = $retrieveData['area']['center']['longitude'] ?? null;

        PhoneBaseline::create([
         'phone_number' => $cleanPhone,
         'latitude' => $latitude,
         'longitude' => $longitude,
        ]);

        $check->update([
        'location_consistent' => true,
        'location_country' => null,
        'location_city' => null,
    ]);
    } else {
        $verifyResponse = Http::retry(2, 1000)->timeout(30)->connectTimeout(15)->withHeaders([
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
                'latitude' => $baseline->latitude,
                'longitude' => $baseline->longitude,
            ],
            'radius' => 50000,
        ],
    ]);

        $verifyData = $verifyResponse->json() ?? [];

        $check->update([
           'location_consistent' => ($verifyData['verificationResult'] ?? 'FALSE') === 'TRUE',
           'location_country' => null,
           'location_city' => null,
      ]);
    }

    return response()->json([
        'location_consistent' => $check->location_consistent,
        'location_country' => $check->location_country,
        'location_city' => $check->location_city,
    ]);
}
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeviceStatusController extends Controller
{
    // Owner: Meriem
    public function check(Request $request)
    {
        $check = TrustCheck::findOrFail($request->trust_check_id);

        $response = Http::withHeaders([
                'X-RapidAPI-Key'  => config('services.nokia_device_status.key'),
                'X-RapidAPI-Host' => config('services.nokia_device_status.host'),
            ])
            ->post(config('services.nokia_device_status.url'), [
                'device' => [
                    'phoneNumber' => $check->phone_number,
                ],
            ]);

            // dd($response->status(), $response->body());
        $data = $response->json() ?? [];

        $connectivityStatus = $data['connectivityStatus'] ?? null;

        $check->update([
            'device_known' => $connectivityStatus !== null && $connectivityStatus !== 'NOT_CONNECTED',
            'connectivity_status' => $connectivityStatus,
        ]);

        return response()->json([
            'device_known' => $check->device_known,
            'connectivity_status' => $check->connectivity_status,
        ]);
    }
}
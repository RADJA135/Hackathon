<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeviceStatusController extends Controller
{
    // Owner: Semsoum
    public function check(Request $request)
    {
        $check = TrustCheck::findOrFail($request->trust_check_id);

        // MOCK — active until NOKIA_DEVICE_STATUS_API_KEY is filled in below.
        if (empty(env('NOKIA_DEVICE_STATUS_API_KEY'))) {
            sleep(1);
            $check->update([
                'device_known' => true,
                'device_id' => 'mock-device-001',
            ]);
            return response()->json([
                'device_known' => $check->device_known,
                'device_id' => $check->device_id,
            ]);
        }

        // REAL Nokia call — activates automatically once the key above is set.
        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => env('NOKIA_DEVICE_STATUS_HOST'),
                'x-rapidapi-key' => env('NOKIA_DEVICE_STATUS_API_KEY'),
            ])
            ->post(env('NOKIA_DEVICE_STATUS_URL'), [
                'phoneNumber' => $check->phone_number,
            ]);

        $data = $response->json() ?? [];

        // TODO Semsoum: confirm real field name via Example Responses tab,
        // same way Radja did for SIM Swap — adjust 'known' below if different.
        $check->update([
            'device_known' => $data['known'] ?? true,
            'device_id' => $data['deviceId'] ?? null,
        ]);

        return response()->json([
            'device_known' => $check->device_known,
            'device_id' => $check->device_id,
        ]);
    }
}
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

        $response = Http::withToken(env('NOKIA_API_KEY'))
            ->post(env('NOKIA_BASE_URL').'/device-status/v0/check', [
                'phoneNumber' => $check->phone_number,
            ]);

        // TODO: replace with real Nokia NaC response field names once you've
        // registered and seen the actual sandbox response shape.
        $data = $response->json() ?? [];

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

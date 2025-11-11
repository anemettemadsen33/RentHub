<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
            'expirationTime' => 'nullable',
            'keys.p256dh' => 'nullable|string',
            'keys.auth' => 'nullable|string',
        ]);

        $endpoint = $data['endpoint'];
        $p256dh = data_get($data, 'keys.p256dh');
        $auth = data_get($data, 'keys.auth');

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => Auth::id(),
                'public_key' => $p256dh,
                'auth_token' => $auth,
                'content_encoding' => 'aesgcm',
            ]
        );

        return response()->json(['status' => 'subscribed', 'id' => $subscription->id]);
    }

    public function unsubscribe(Request $request)
    {
        $endpoint = $request->input('endpoint');
        if (! $endpoint) {
            return response()->json(['message' => 'endpoint required'], 422);
        }

        $deleted = PushSubscription::where('endpoint', $endpoint)->delete();

        return response()->json(['status' => 'unsubscribed', 'deleted' => $deleted]);
    }
}


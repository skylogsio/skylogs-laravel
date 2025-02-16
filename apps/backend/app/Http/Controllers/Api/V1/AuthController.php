<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Jobs\SendNotifyJob;
use App\Models\AlertRule;
use App\Models\MetabaseWebhookAlert;
use App\Models\SentryWebhookAlert;
use App\Models\ZabbixWebhookAlert;
use App\Services\GrafanaService;
use App\Services\SendNotifyService;
use Illuminate\Http\Request;


class AuthController extends Controller
{


    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $refreshToken = auth()->claims(['refresh' => true])->tokenById(auth()->id());

        return $this->respondWithTokens($token, $refreshToken);
    }

    public function refresh(Request $request)
    {
        try {
            // Verify that the token has a "refresh" claim
            $refreshToken = $request->header('Authorization');
            $refreshToken = str_replace('Bearer ', '', $refreshToken);

            $payload = auth()->setToken($refreshToken)->getPayload();

            if (!$payload->get('refresh')) {
                return response()->json(['message' => 'Invalid refresh token'], 401);
            }

            $newAccessToken = auth()->tokenById($payload->get('sub'));
            $newRefreshToken = auth()->claims(['refresh' => true])->setTTL(43200)->tokenById($payload->get('sub'));

            return $this->respondWithTokens($newAccessToken, $newRefreshToken);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }
    }

    protected function respondWithTokens($accessToken, $refreshToken)
    {
        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'refresh_expires_in' => 43200 * 60,
        ]);
    }


    public function me()
    {
        return response()->json(auth()->user());
    }


    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


}

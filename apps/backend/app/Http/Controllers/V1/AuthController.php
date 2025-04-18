<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class AuthController extends Controller
{


    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $refreshToken = auth()->claims(['refresh' => true])
            ->setTTL(config("jwt.refresh_ttl"))
            ->tokenById(auth()->id());

        return $this->respondWithTokens($token, $refreshToken);
    }

    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->header('Authorization');
            $refreshToken = str_replace('Bearer ', '', $refreshToken);

            $payload = auth()->setToken($refreshToken)->getPayload();

            if (!$payload->get('refresh')) {
                return response()->json(['message' => 'Invalid refresh token'], 401);
            }

            $newAccessToken = auth()->tokenById($payload->get('sub'));
            $newRefreshToken = auth()->claims(['refresh' => true])
                ->setTTL(config("jwt.refresh_ttl"))
                ->tokenById($payload->get('sub'));

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
            'expires_in' => config("jwt.ttl") * 60,
            'refresh_expires_in' => config("jwt.refresh_ttl") * 60,
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

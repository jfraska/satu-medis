<?php

namespace App\Http\Controllers;

use App\Http\Helper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use Helper;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], $this->httpMessage['StatusUnprocessableEntity'], $validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = auth()->login($user);

        return  $this->responseFormatter($this->httpCode['StatusOK'], $this->httpMessage['StatusOK'],
        [
            'user' => $user,
            'access_token' => [
                'token' => $token,
                'type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,    // get token expires in seconds
            ],
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], $this->httpMessage['StatusUnprocessableEntity'], $validator->errors());
        }

        $token = JWTAuth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!$token) {
            return  $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], "User not found");
        }

        return  $this->responseFormatter($this->httpCode['StatusOK'], $this->httpMessage['StatusOK'],
        [
            'user' => auth()->user(),
            'access_token' => [
                'token' => $token,
                'type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
            ],
        ]);
    }

    public function logout()
    {
        $token = JWTAuth::getToken();

        $invalidate = JWTAuth::invalidate($token);

        if($invalidate) {
            return $this->responseFormatter($this->httpCode['StatusOK'], "Successfully logged out");
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return  $this->responseFormatter($this->httpCode['StatusOK'], $this->httpMessage['StatusOK'], auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return
        $this->responseFormatter($this->httpCode['StatusOK'], $this->httpMessage['StatusOK'], [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}

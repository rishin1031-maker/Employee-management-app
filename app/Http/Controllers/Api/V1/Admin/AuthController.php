<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        if (!$token = auth('api_admin')->attempt($validator->validated())) {
            return $this->error('Invalid credentials.', 401);
        }

        return $this->success(
            ApiTransformer::tokenResponse(
                $token,
                auth('api_admin')->user(),
                fn ($u) => ApiTransformer::admin($u),
                (int) config('jwt.ttl', 60)
            ),
            'Login successful.'
        );
    }

    public function me(): JsonResponse
    {
        return $this->success(ApiTransformer::admin(auth('api_admin')->user()));
    }

    public function refresh(): JsonResponse
    {
        $token = auth('api_admin')->refresh();

        return $this->success(
            ApiTransformer::tokenResponse(
                $token,
                auth('api_admin')->user(),
                fn ($u) => ApiTransformer::admin($u),
                (int) config('jwt.ttl', 60)
            ),
            'Token refreshed.'
        );
    }

    public function logout(): JsonResponse
    {
        auth('api_admin')->logout();

        return $this->success(null, 'Logged out successfully.');
    }
}

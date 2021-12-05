<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    use ApiResponseTrait;

    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                "name" => $request->get("name"),
                "birthday" => $request->get("birthday"),
                "gender" => $request->get("gender"),
                "avatar_url" => $request->get("avatar_url"),
                "email" => $request->get("email"),
                "password" => Hash::make($request->get("password")),
            ]);

            Auth::login($user);

            return $this->successResponse([
                "user" => Auth::user()
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                "exception" => get_class($e),
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            if (Auth::attempt($request->only("email", "password"))) {
                return $this->successResponse([
                    "user" => Auth::user()
                ]);
            }

            return $this->successResponse([
                "user" => null
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                "exception" => get_class($e),
                "message" => $e->getMessage()
            ], 500);
        }
    }
}

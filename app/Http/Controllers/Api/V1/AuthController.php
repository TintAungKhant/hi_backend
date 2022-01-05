<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponseTrait;
use Exception;

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
                "user" => Auth::user(),
                "token" => Auth::user()->createToken(Auth::user()->id)->plainTextToken
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            if (Auth::attempt($request->only("email", "password"))) {
                return $this->successResponse([
                    "user" => Auth::user(),
                    "token" => Auth::user()->createToken(Auth::user()->id)->plainTextToken
                ]);
            }

            return $this->failResponse([
                "message" => "Wrong login credentials."
            ], 401);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function logout()
    {
        try {
            Auth::logout();

            return $this->successResponse([
                "user" => []
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

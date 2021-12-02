<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    use ApiResponseTrait;

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "name" => "required",
                "birthday" => "required",
                "email" => "required|email|unique:users",
                "password" => "required|confirmed"
            ]);

            if ($validator->fails()) {
                return $this->errorResponse([
                    "errors" => $validator->errors()
                ], 422);
            }

            $user = User::create([
                "name" => $request->get("name"),
                "birthday" => $request->get("birthday"),
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
}

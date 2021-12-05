<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends BaseController
{
    use ApiResponseTrait;

    public function get(Request $request)
    {
        $user = null;

        if($request->filled("id")){
            $user = User::find($request->get("id"));
        }else{
            $user = $this->auth_user;
        }

        if(!$user){
            return $this->errorResponse([], 200);
        }

        return $this->successResponse([
            "profile" => $user->load("profile_images")
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            "name" => "required",
            "gender" => "required",
            "birthday" => "required"
        ];

        if ($request->filled("password")) {
            $rules["password"] = "confirmed";
        }

        if (!$this->validator($request->all(), $rules)) {
            return $this->errorResponse([
                "errors" => $this->validation_errors
            ], 422);
        }

        $update_data = [
            "name" => $request->get("name"),
            "gender" => $request->get("gender"),
            "birthday" => $request->get("birthday")
        ];

        if ($request->filled("password")) {
            $update_data["password"] = Hash::make($request->get("password"));
        }

        $this->auth_user->update($update_data);

        $this->auth_user->refresh();

        return $this->successResponse([
            "profile" => $this->auth_user
        ]);
    }
}

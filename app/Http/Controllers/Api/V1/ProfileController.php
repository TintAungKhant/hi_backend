<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

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
}

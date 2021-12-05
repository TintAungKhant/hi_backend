<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\GetProfileRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Models\ProfileImage;
use App\Models\User;
use App\Services\FileUpload;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends BaseController
{
    use ApiResponseTrait;

    public function get(GetProfileRequest $request)
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

    public function update(UpdateProfileRequest $request)
    {
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

    public function updateProfileImage(Request $request)
    {
        // TODO:: change request
        $rules = [
            "type" => "required|integer",
            "image" => "nullable|image",
            "old_image_id" => "nullable|integer"
        ];

        $type = $request->get("type");
        if ($type == 1 || $type == 2) {

            if ($type == 1) {
                if ($this->auth_user->profile_images()->where("type", ProfileImage::PROFILE_IMAGE_TYPES["profile"])->count() == 1) {
                    $rules["old_image_id"] = "required|integer";
                }
            } else {
                if ($this->auth_user->profile_images()->where("type", ProfileImage::PROFILE_IMAGE_TYPES["featured"])->count() == 5) {
                    $rules["old_image_id"] = "required|integer";
                }
            }

            if (!$this->validator($request->all(), $rules)) {
                return $this->errorResponse([
                    "errors" => $this->validation_errors
                ], 422);
            }

            if ($request->hasFile("image")) {
                $fileUpload = new FileUpload;
                $file_path = $fileUpload->save($request->file("image"), "image");

                if ($request->filled("old_image_id")) {
                    $image = $this->auth_user->profile_images()->where(function ($q) use ($type) {
                        if ($type == 1) {
                            $q->where("type", ProfileImage::PROFILE_IMAGE_TYPES["profile"]);
                        } else {
                            $q->where("type", ProfileImage::PROFILE_IMAGE_TYPES["featured"]);
                        }
                    })->where("id", $request->get("old_image_id"))->first();

                    if (!$image) {
                        return $this->errorResponse([], 200);
                    }

                    $image->update([
                        "url" => env("APP_URL") . $file_path
                    ]);

                    $image->refresh();
                } else {
                    $image = $this->auth_user->profile_images()->create([
                        "url" => env("APP_URL") . $file_path,
                        "type" => $type == 1 ? ProfileImage::PROFILE_IMAGE_TYPES["profile"] : ProfileImage::PROFILE_IMAGE_TYPES["featured"]
                    ]);
                }

                return $this->successResponse([
                    "image" => $image
                ]);
            }
        }

        return $this->errorResponse([], 200);
    }
}

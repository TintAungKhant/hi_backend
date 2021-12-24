<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Requests\Api\V1\UpdateProfileImageRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Models\ProfileImage;
use App\Models\User;
use App\Services\FileUpload;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Hash;

class ProfileController extends BaseController
{
    use ApiResponseTrait;

    public function get($user_id = null)
    {
        try {
            $user = null;

            if ($user_id) {
                $user = User::find($user_id);
            } else {
                $user = $this->auth_user;
            }

            if (!$user) {
                return $this->failResponse([
                    "message" => "User not found."
                ], 404);
            }

            return $this->successResponse([
                "profile" => $user->load("profile_images")
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function update(UpdateProfileRequest $request)
    {
        try {
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
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function updateProfileImage(UpdateProfileImageRequest $request)
    {
        try {
            $type = $request->get("type");
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
                        return $this->failResponse([
                            "message" => "Image not found."
                        ], 404);
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

            return $this->failResponse([
                "message" => "Invalid parameters."
            ], 400);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

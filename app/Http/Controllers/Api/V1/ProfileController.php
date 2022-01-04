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

                $contact = $this->auth_user->getContact($user);

                $contact_type = null;

                if ($contact) {
                    if ($contact->pivot->type == User::CONTACT_USER_TYPES["deciding"]) {
                    }
                    switch ($contact->pivot->type) {
                        case User::CONTACT_USER_TYPES["deciding"]:
                            $contact_type = "deciding";
                            break;

                        case User::CONTACT_USER_TYPES["friend"]:
                            $contact_type = "friend";
                            break;

                        default:
                            $contact_type = "waiting";
                            break;
                    }
                }

                $user->contact_type = $contact_type;
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

                if ($request->hasFile("image")) {
                    $fileUpload = new FileUpload;
                    $file_path = $fileUpload->save($request->file("image"), "image");

                    $image->update([
                        "url" => $file_path
                    ]);

                    $image->refresh();

                    return $this->successResponse([
                        "image" => $image
                    ]);
                } else {
                    $image->delete();

                    return $this->successResponse([
                        "image" => null
                    ]);
                }
            } else {
                if ($request->hasFile("image")) {
                    $fileUpload = new FileUpload;
                    $file_path = $fileUpload->save($request->file("image"), "image");

                    $image = $this->auth_user->profile_images()->create([
                        "url" => $file_path,
                        "type" => $type == 1 ? ProfileImage::PROFILE_IMAGE_TYPES["profile"] : ProfileImage::PROFILE_IMAGE_TYPES["featured"]
                    ]);

                    return $this->successResponse([
                        "image" => $image
                    ]);
                }
            }

            return $this->failResponse([
                "message" => "Invalid parameters."
            ], 400);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

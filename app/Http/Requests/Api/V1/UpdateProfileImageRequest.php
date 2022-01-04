<?php

namespace App\Http\Requests\Api\V1;

use App\Models\ProfileImage;
use Illuminate\Support\Facades\Auth;

class UpdateProfileImageRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            "type" => "required|integer",
            "image" => "sometimes|image|max:2048",
            "old_image_id" => "sometimes|integer"
        ];

        $type = request()->get("type");

        if ($type == 1) {
            if (Auth::user()->profile_images()->where("type", ProfileImage::PROFILE_IMAGE_TYPES["profile"])->count() == 1) {
                $rules["old_image_id"] = "required|integer";
            }
        } else {
            if (Auth::user()->profile_images()->where("type", ProfileImage::PROFILE_IMAGE_TYPES["featured"])->count() == 5) {
                $rules["old_image_id"] = "required|integer";
            }
        }

        return $rules;
    }
}

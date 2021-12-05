<?php

namespace App\Http\Requests\Api\V1;

class UpdateProfileRequest extends BaseRequest
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
        return [
            "name" => "required",
            "gender" => "required",
            "birthday" => "required",
            "password" => "nullable|confirmed"
        ];
    }
}

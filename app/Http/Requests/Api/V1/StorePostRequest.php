<?php

namespace App\Http\Requests\Api\V1;

class StorePostRequest extends BaseRequest
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
            "content" => "required|string|max:1800",
            "image" => "sometimes|image|max:2048",
        ];
    }
}

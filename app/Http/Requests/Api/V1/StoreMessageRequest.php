<?php

namespace App\Http\Requests\Api\V1;

use App\Rules\Must;

class StoreMessageRequest extends BaseRequest
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
            "type" => ["required", new Must("text", "image")]
        ];
    }
}

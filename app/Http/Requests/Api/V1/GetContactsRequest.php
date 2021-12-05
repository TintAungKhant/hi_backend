<?php

namespace App\Http\Requests\Api\V1;

class GetContactsRequest extends BaseRequest
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
            "page" => "nullable|integer|min:1",
            "limit" => "nullable|integer|min:1"
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1;

class GetConversationRequest extends BaseRequest
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
            "user_id" => "required_without:conversation_id|integer",
            "conversation_id" => "required_without:user_id|integer",
        ];
    }
}

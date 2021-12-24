<?php

namespace App\Http\Requests\Api\V1;

class GetMessagesRequest extends BaseRequest
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
            "last_message_id" => "sometimes|integer",
            "user_id" => "required_without:conversation_id|integer",
            "conversation_id" => "required_without:user_id|integer",
        ];
    }
}

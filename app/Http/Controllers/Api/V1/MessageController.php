<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Requests\Api\V1\GetMessagesRequest;
use App\Models\Message;
use App\Traits\ApiResponseTrait;
use Exception;

class MessageController extends BaseController
{
    use ApiResponseTrait;

    public function get(GetMessagesRequest $request, $conversation_id)
    {
        try {
            $last_message = null;
            if ($request->get("last_message_id")) {
                $last_message = Message::find($request->get("last_message_id"));
            }

            $conversation = $this->auth_user->getConversation($conversation_id);

            if (!$conversation) {
                return $this->errorResponse([
                    "message" => "Invalid request."
                ], 400);
            }

            $conversation->getMessages($last_message);

            return $this->successResponse([
                "conversation" => $conversation
            ]);

            return $this->successResponse([
                "conversation" => []
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

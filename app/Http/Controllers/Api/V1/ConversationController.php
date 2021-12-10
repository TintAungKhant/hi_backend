<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Requests\Api\V1\GetConversationsRequest;
use App\Models\Conversation;
use App\Models\TextMessage;
use App\Traits\ApiResponseTrait;
use Exception;

class ConversationController extends BaseController
{
    use ApiResponseTrait;

    public function get(GetConversationsRequest $request)
    {
        try {
            $last_conversation = null;
            if ($request->filled("last_conversation_id")) {
                $last_conversation = Conversation::find($request->get("last_conversation_id"));
            }
            $conversations = $this->auth_user->getConversations($last_conversation);

            $conversations->each(function($conversation){
                if($conversation->latest_message->messageable_type == TextMessage::class){
                    $conversation->latest_message->type = "text";
                }else{
                    $conversation->latest_message->type = "image";
                }
            });

            return $this->successResponse([
                "conversations" => $conversations
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

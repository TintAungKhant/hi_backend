<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Requests\Api\V1\GetConversationRequest;
use App\Http\Requests\Api\V1\GetConversationsRequest;
use App\Models\Conversation;
use App\Models\TextMessage;
use App\Models\User;
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

            $conversations->each(function ($conversation) {
                if ($conversation->latest_message->messageable_type == TextMessage::class) {
                    $conversation->latest_message->type = "text";
                } else {
                    $conversation->latest_message->type = "image";
                }
                $conversation->latest_message->makeHidden("messageable_id","messageable_type");
            });

            return $this->successResponse([
                "conversations" => $conversations
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function show(GetConversationRequest $request)
    {
        try {
            $conversation_id = $request->get("conversation_id");

            if ($request->filled("user_id")) {
                $user = User::find($request->get("user_id"));

                if (!$user) {
                    return $this->failResponse([
                        "message" => "User not found."
                    ], 404);
                } else if ($user->id == $this->auth_user->id) {
                    return $this->failResponse([
                        "message" => "Cant chat yourself."
                    ], 400);
                }

                $conversation = $this->auth_user->firstOrCreateConversation($user);

                if(!$conversation){
                    return $this->failResponse([
                        "message" => "User is not friend with you."
                    ], 400);
                }

                $conversation_id = $conversation->id;
            }

            $conversation = $this->auth_user->getConversation($conversation_id);

            return $this->successResponse([
                "conversation" => $conversation
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

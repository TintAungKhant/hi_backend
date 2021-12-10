<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GetMessagesRequest;
use App\Http\Requests\Api\V1\StoreMessageRequest;
use App\Models\ImageMessage;
use App\Models\Message;
use App\Models\TextMessage;
use App\Services\FileUpload;
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

    public function store(StoreMessageRequest $request, $conversation_id)
    {
        try {
            $conversation = $this->auth_user->getConversation($conversation_id);
            if (!$conversation) {
                return $this->failResponse([
                    "message" => "Invalid request"
                ], 400);
            }

            $message = Message::make([]);

            if ($request->get("type") == "text") {
                $messageable = TextMessage::create([
                    "text" => $request->get("text")
                ]);
            } else if ($request->get("type") == "image") {
                $image_path = (new FileUpload())->save($request->file("file"), "image");
                $messageable = ImageMessage::create([
                    "image_path" => $image_path
                ]);
            }

            $message->messageable()->associate($messageable);

            $message->user()->associate($this->auth_user);
            $message->conversation()->associate($conversation);

            $message->save();

            $conversation->updated_at = $message->created_at;
            $conversation->update();

            $conversation->load([
                "latest_message"
            ]);

            return $this->successResponse([
                "message" => $message
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

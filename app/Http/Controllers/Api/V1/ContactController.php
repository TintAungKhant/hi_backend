<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ContactController extends BaseController
{
    use ApiResponseTrait;

    public function explore(Request $request)
    {
        if (
            !$this->validator($request->all(), [
                "gender" => "nullable|integer"
            ])
        ) {
            return $this->errorResponse([
                "errors" => $this->validation_errors
            ], 422);
        }

        $contacts = $this->auth_user->getNewContacts($request->get("gender"));

        return $this->successResponse([
            "contacts" => $contacts
        ]);
    }

    public function contacts(Request $request)
    {
        if (
            !$this->validator($request->all(), [
                "page" => "nullable|integer|min:1",
                "limit" => "nullable|integer|min:1"
            ])
        ) {
            return $this->errorResponse([
                "errors" => $this->validation_errors
            ], 422);
        }

        $contacts = $this->auth_user->getContacts($request->get("page", 1), $request->get("limit", 20), $request->get("type"));

        return $this->successResponse([
            "contacts" => $contacts
        ]);
    }

    public function add(Request $request)
    {
        if (
            !$this->validator($request->all(), [
                "id" => "required"
            ])
        ) {
            return $this->errorResponse([
                "errors" => $this->validation_errors
            ], 422);
        }

        $user = User::find($request->get("id"));
        if ($user) {
            $existing_contact = $this->auth_user->getContact($user);
            if (!$existing_contact) {
                $this->auth_user->addContact($user);

                return $this->successResponse([]);
            }
        }

        return $this->errorResponse([], 200);
    }

    public function accept(Request $request)
    {
        if (
            !$this->validator($request->all(), [
                "id" => "required"
            ])
        ) {
            return $this->errorResponse([
                "errors" => $this->validation_errors
            ], 422);
        }

        $user = User::find($request->get("id"));
        if ($user) {
            $existing_contact = $this->auth_user->getContact($user, 2);
            if ($existing_contact) {
                $this->auth_user->acceptContact($user);

                return $this->successResponse([]);
            }
        }

        return $this->errorResponse([], 200);
    }
}

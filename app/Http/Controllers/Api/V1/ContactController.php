<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Requests\Api\V1\ExploreContactsRequest;
use App\Http\Requests\Api\V1\GetContactsRequest;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;

class ContactController extends BaseController
{
    use ApiResponseTrait;

    public function explore(ExploreContactsRequest $request)
    {
        try {
            $contacts = $this->auth_user->getNewContacts($request->get("gender"));

            return $this->successResponse([
                "contacts" => $contacts
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function contacts(GetContactsRequest $request)
    {
        try {
            $contacts = $this->auth_user->getContacts($request->get("type"), $request->get("page", 1));

            return $this->successResponse([
                "contacts" => $contacts
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function add($user_id)
    {
        try {
            $user = User::find($user_id);
            if ($user) {
                $existing_contact = $this->auth_user->getContact($user);
                if (!$existing_contact) {
                    $this->auth_user->addContact($user);

                    return $this->successResponse([]);
                }

                return $this->failResponse([
                    "message" => "Contact already existed."
                ], 400);
            }

            return $this->failResponse([
                "message" => "User not found."
            ], 404);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function accept($user_id)
    {
        try {
            $user = User::find($user_id);
            if ($user) {
                $existing_contact = $this->auth_user->getContact($user, 2);
                if ($existing_contact) {
                    $this->auth_user->acceptContact($user);

                    return $this->successResponse([]);
                }

                return $this->failResponse([
                    "message" => "Contact not found."
                ], 404);
            }

            return $this->failResponse([
                "message" => "User not found."
            ], 404);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function delete($user_id)
    {
        try {
            $user = User::find($user_id);
            if ($user) {
                $existing_contact = $this->auth_user->getContact($user);
                if ($existing_contact) {
                    $this->auth_user->deleteContact($user);

                    return $this->successResponse([]);
                }

                return $this->failResponse([
                    "message" => "Contact not found."
                ], 404);
            }

            return $this->failResponse([
                "message" => "User not found."
            ], 404);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}

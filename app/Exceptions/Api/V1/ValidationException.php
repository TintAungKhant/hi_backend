<?php

namespace App\Exceptions\Api\V1;

use Exception;
use Illuminate\Http\JsonResponse;

class ValidationException extends Exception
{
    protected $response;

    public function __construct(JsonResponse $response)
    {
        $this->response = $response;
    }

    public function render()
    {
        return $this->response;
    }
}

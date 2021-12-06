<?php

namespace App\Exceptions\Api\V1;

use App\Traits\ApiResponseTrait;
use Exception;
use \Illuminate\Contracts\Validation\Validator;

class ValidationException extends Exception
{
    use ApiResponseTrait;

    protected $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function render()
    {
        return $this->failResponse([
            "errors" => $this->validator->errors()
        ], 422);
    }
}

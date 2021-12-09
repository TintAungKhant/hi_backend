<?php

namespace App\Exceptions\Api\V1;

use App\Traits\ApiResponseTrait;
use Exception;

class InternalErrorException extends Exception
{
    use ApiResponseTrait;

    protected $e;

    public function __construct(Exception $e)
    {
        $this->e = $e;
    }

    public function render()
    {
        $data = [
            "message" => "System error occurs"
        ];

        if(env("APP_ENV", "local") == "local"){
            $data = [
                "message" => $this->e->getMessage(),
                "exception" => self::class,
                "file" => $this->e->getFile(),
                "line" => $this->e->getLine(),
            ];
        }

        return $this->errorResponse($data, 500);
    }
}

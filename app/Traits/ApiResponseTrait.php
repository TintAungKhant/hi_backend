<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse($data = [])
    {
        return response()->json([
            "status" => "success",
            "data" => $data
        ], 200);
    }

    public function failResponse($data = [], $code)
    {
        return response()->json([
            "status" => "fail",
            "data" => $data
        ], $code);
    }

    public function errorResponse($message="", $code)
    {
        return response()->json([
            "status" => "error",
            "message" => $message
        ], $code);
    }
}

<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse(array $data = [])
    {
        return $this->sendResponse([
            "status" => "success",
            "data" => $data
        ], 200);
    }

    public function failResponse(array $data = [], int $code)
    {
        return $this->sendResponse([
            "status" => "fail",
            "data" => $data
        ], $code);
    }

    public function errorResponse(array $data = [], int $code)
    {
        return $this->sendResponse(
            array_merge(["status" => "error",], $data),
            $code
        );
    }

    private function sendResponse(array $data, int $code)
    {
        return response()->json($data, $code);
    }
}

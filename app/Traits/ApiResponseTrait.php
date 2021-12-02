<?php

namespace App\Traits;

trait ApiResponseTrait{
    public function successResponse($data=[]){
        return $this->sendResponse(true, $data);
    }

    public function errorResponse($data=[], $status){
        return $this->sendResponse(false, $data, $status);
    }

    public function sendResponse($success, $data=[], $status=200){
        return response()->json([
            "success" => $success,
            "data" => $data
        ], $status);
    }
}
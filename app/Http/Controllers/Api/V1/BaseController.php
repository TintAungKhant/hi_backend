<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{
    protected $auth_user = null;
    protected $validation_errors = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->auth_user = Auth::user();
            return $next($request);
        });
    }

    public function validator(array $params, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = Validator::make($params, $rules, $messages, $customAttributes);

        if($validator->fails()){
            $this->validation_errors = $validator->errors();
            return false;
        }
        return true;
    }
}

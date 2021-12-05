<?php

namespace App\Http\Requests\Api\V1;

use App\Exceptions\Api\V1\ValidationException;
use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use \Illuminate\Contracts\Validation\Validator;
use \Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    use ApiResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $this->errorResponse([
                "errors" => $validator->errors()
            ], 422)
        );
    }
}

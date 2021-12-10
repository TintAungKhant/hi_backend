<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Must implements Rule
{
    protected $values;
    protected $attribute;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->values = func_get_args();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        
        return in_array($value, $this->values);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $msg = "The " . $this->attribute . " field must be ";
        foreach ($this->values as $key => $value) {
            $msg .= $value;
            if ($key + 1 < count($this->values)) {
                $msg .= " or ";
            } else {
                $msg .= ".";
            }
        }
        return $msg;
    }
}

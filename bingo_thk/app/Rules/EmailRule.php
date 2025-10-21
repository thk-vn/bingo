<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            $fail( __("view.rule.validate.email") );
            return;
        }

        $domain = substr(strrchr($value, "@"), 1);

        $domain_pattern = "thk-hd.vn";
        if (strtolower($domain) !== $domain_pattern)
        {
            $fail( __("view.rule.validate.email_pattern"));
        }
    }
}

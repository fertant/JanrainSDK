<?php
/** MIT License */
namespace janrain\settings;

class IsUrl extends Validator
{
    public function __invoke(&$val)
    {
        return (bool) filter_var($val, FILTER_VALIDATE_URL);
    }
}

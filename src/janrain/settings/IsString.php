<?php
/** MIT License */
namespace janrain\settings;

class IsString extends Validator
{
    public function __invoke(&$val)
    {
        return is_string($val);
    }
}

<?php
/** MIT License */
namespace janrain\settings;

class NotNull extends Validator
{
    public function __invoke(&$val)
    {
        return !is_null($val);
    }
}

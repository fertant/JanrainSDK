<?php
/** MIT License */
namespace janrain\settings;

class NotEmpty extends Validator
{
    public function __invoke(&$val)
    {
        return !empty($val);
    }
}

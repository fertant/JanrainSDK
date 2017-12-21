<?php
/** MIT License */
namespace janrain\settings;

class IsStringArray extends Validator
{
    public function __invoke(&$val)
    {
        // quick fail if not array
        if (!is_array($val)) {
            return false;
        }

        // fail if any value isn't a string
        foreach ($val as &$v) {
            if (!is_string($v)) {
                // found non-string
                return false;
            }
        }
        return true;
    }
}

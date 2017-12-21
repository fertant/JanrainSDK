<?php
namespace janrain\settings;

use janrain\Sdk;

class Opts
{
    const TYPE_USER = 'OPTS_U';
    const TYPE_JANRAIN = 'OPTS_J';

    protected $features;
    protected $cms;

    public function __construct(Sdk $sdk)
    {
        $this->features = $sdk->getFeatures();
        $this->cms = $sdk->getConfig();
    }

    private function getOptValidators($optsType)
    {
        $validators = array();
        foreach ($this->features as $f) {
            $validators = array_merge_recursive($validators, $f::getValidators($optsType));
        }
        foreach ($validators as $k => &$v) {
            $v = array_unique($v);
        }
        return $validators;
    }

    public function getUserErrorCodes()
    {
        $validators = $this->getOptValidators(self::TYPE_USER);
        foreach ($this->features as $f) {
            if ($f->getName() == 'CaptureApi') {
                // when capture is in play, apiKey is determined AFTER processing capture credentials
                // handled by CaptureApi putting apiKey in OPTS_J
                unset($validators['apiKey']);
                break;
            }
        }
        return $this->getErrorCodes($validators);
    }

    public function getJanrainErrorCodes()
    {
        $validators = $this->getOptValidators(self::TYPE_JANRAIN);
        foreach ($this->features as $f) {
            if ($f->getName() == 'CaptureApi') {
                //when capture is in play, token_url is not meaningful
                unset($validators['token_uri']);
                break;
            }
        }
        return $this->getErrorCodes($validators);
    }

    private function getErrorCodes($validators)
    {
        $errors = array();
        foreach ($validators as $opt => $checks) {
            $value = $this->cms->getItem($opt);
            foreach ($checks as $check) {
                $check = Validator::fromName($check);
                if (!$check($value)) {
                    $errors[$opt][] = $check->getFailCode();
                }
            }
        }
        return $errors;
    }
}

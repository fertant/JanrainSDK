<?php
/** MIT License */
namespace janrain;

use janrain\Adapter;

abstract class Feature implements \ArrayAccess
{
    protected $config;

    public function __construct(Adapter $cms)
    {
        $this->config = $cms;
    }

    protected static $OPTS_U = array('features' => array('IsStringArray'));
    protected static $OPTS_J = array();

    public static function getValidators($type)
    {
        if ($class = get_parent_class(get_called_class())) {
            return array_merge_recursive(static::${$type}, $class::getValidators($type));
        }
        return static::${$type};
    }

    public static function forName($name, Adapter $p)
    {
        if (!class_exists($name)) {
            $name = sprintf('janrain\features\%s', $name);
        }
        if (is_subclass_of($name, __CLASS__)) {
            return new $name($p);
        }
        throw new \DomainException("$name not subclass of " . __CLASS__);
    }

    public function getName()
    {
        return substr(strrchr(get_class($this), '\\'), 1);
    }

    /**
     * {@inheritdoc}
     *
     * Implements ArrayAccess
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->config->getItem($offset);
        }
        $msg = sprintf(
            '%s undefined for this feature.',
            htmlspecialchars($offset, ENT_QUOTES)
        );
        throw new \DomainException($msg);
    }

    /**
     * @todo validate this offset
     */
    public function offsetSet($offset, $value)
    {
        $this->config->setItem($offset, $value);
    }

    public function offsetExists($offset)
    {
        static $keys = null;
        if (is_null($keys)) {
            $declaredSettings = array_merge_recursive(
                $this::getValidators('OPTS_U'),
                $this::getValidators('OPTS_J')
            );
            $keys = array_keys($declaredSettings);
        }
        return in_array($offset, $keys);
    }

    /** NOOP */
    public function offsetUnset($offset)
    {
    }

    // Utility for subclasses to sanitize js setting string values
    protected function cleanSetting($val)
    {
        // Raw ints are okay for js
        if (is_int($val)) {
            return $val;
        }
        // Native bools need to be "wordified" or they come out 1 and ''
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }
        // Strings need apostrophe, double-quote, and front-slash escaped to be "safe" js strings.
        return str_replace(array("'",'"','/'), array('\\x27', '\\x22', '\\x2F'), $val);
    }

    // utility for cleaning html attributes for tags.  ENCODE ALL THE THINGS!
    protected function cleanAttr($val)
    {
        return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
    }
}

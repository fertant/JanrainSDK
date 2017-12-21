<?php
/** MIT License */
namespace janrain\settings;

abstract class Validator
{
    abstract public function __invoke(&$val);

    public function getFailCode()
    {
        $class = substr(strrchr(get_called_class(), '\\'), 1);
        if (0 === stripos($class, 'Not')) {
            return 'Is' . substr($class, 3);
        }
        return 'Not' . substr($class, 2);
    }

    #factory to create validators by short names
    private static $cache = array();

    public static function fromName($name)
    {
        $className = __NAMESPACE__ . '\\' . $name;
        if (!array_key_exists($name, self::$cache)) {
            self::$cache[$name] = new $className();
        }
        return self::$cache[$name];
    }
}

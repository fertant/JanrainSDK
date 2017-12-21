<?php
/** MIT License */
namespace janrain\adapter;

/**
 * Interface for talking to Application/Framework persistent configuration.
 *
 * In general, each app has it's own mechanism for storing and retrieving
 */
interface Configurable extends \IteratorAggregate
{
    /**
     * Get a configuration item by key name.
     *
     * @param string $key
     *   The Janrain name of the configuration item.  Implementers handle namespace resolution.
     *
     * @return mixed|null
     *   The PHP-native value of the config item or null if the key doesn't exist. Implementors must convert
     *   app-specific conventions to native PHP types, for example: "booleans" such as "true","false",1,0 to PHP true,
     *   false
     */
    public function getItem($key);

    /**
     * Set persistent configuration data.
     *
     * @param string $key
     *   The Janrain name of the configuration item.  Implementers should handle namespacing of keys.
     *
     * @param mixed $value
     *   The value to be stored.  App-specific conversions from PHP native values should be handled by implementers.
     */
    public function setItem($key, $value);

    /**
     * Export all Janrain options in json format.
     *
     * @return string
     *   All config serialized to json.
     */
    public function toJson();
}

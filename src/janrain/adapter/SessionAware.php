<?php
/** MIT License */
namespace janrain\adapter;

/**
 * The contract for interacting with external sessions.
 *
 * In general, each external system has it's own mechanism for storing and
 * retrieving session data.
 */
interface SessionAware
{
    /**
     * Get a session item by key name.
     *
     * @param string $key
     *   Implementers should handle namespace resolution.
     *
     * @return mixed
     *   Should return null if key doesn't exist or is unset.
     *   Should convert application "boolish" to true booleans
     */
    public static function getSessionItem($key);

    /**
     * Set session data.
     *
     * @param string $key
     *   Implementors handle namespace resolution.
     *
     * @param mixed $value
     *   Implementors must convert php-native types to platform appropriate
     *   types.
     */
    public static function setSessionItem($key, $value);


    /**
     * Clear Janrain related session data by key.
     *
     * @param string $key
     *   Implementors handle key namespacing.
     */
    public static function dropSessionItem($key);
}

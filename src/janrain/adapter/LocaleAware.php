<?php
/** MIT License */
namespace janrain\adapter;

/**
 * Represents Locale awareness of the App Adapter
 */
interface LocaleAware
{
    /**
     * Get the current locale of the App Adapter
     *
     * @return string
     *   Returns the RFC-5646 formatted locale string. Examples: en-US, fr-FR, fr-CA
     */
    public function getLocale();
}

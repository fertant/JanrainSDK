<?php
namespace janrain;

use janrain\adapter\Configurable;
use janrain\adapter\LocaleAware;
use janrain\adapter\SessionAware;

/**
 * Adapter interface to the Application/Framework utilizing the SDK
 *
 * This groups all the individual adapter interfaces necessary to allow the Janrain SDK to do the heavy lifting of
 * communicating with Janrain, managing Janrain assets, and handling non-vanilla PHP environments.
 *
 * @todo Create a default "PHP" adapter that shows a basic implementation.  (when SDK is standalone)
 */
interface Adapter extends Configurable, LocaleAware, SessionAware
{
}

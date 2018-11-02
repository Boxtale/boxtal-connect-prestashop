<?php
/**
 * Contains code for environment util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal;

/**
 * Environment util class.
 *
 * Helper to check environment.
 */
class EnvironmentUtil
{

    /**
     * Get warning about PHP version, WC version.
     *
     * @param \boxtalconnect $plugin plugin object.
     *
     * @return string $message
     */
    public static function checkErrors($plugin)
    {
        if (version_compare(PHP_VERSION, $plugin->minPhpVersion, '<')) {
            /* translators: 1) int version 2) int version */
            $message = $plugin->l('Boxtal Connect - the minimum PHP version required for this plugin is %1$s. You are running %2$s.');

            return sprintf($message, $plugin->minPhpVersion, PHP_VERSION);
        }

        return false;
    }
}

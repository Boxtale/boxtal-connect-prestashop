<?php
/**
 * Contains code for configuration util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

/**
 * Configuration util class.
 *
 * Helper to manage configuration.
 */
class ConfigurationUtil
{

    /**
     * Get option.
     *
     * @param string $name    option name.
     * @param mixed  $default option default value.
     *
     * @return string option value.
     */
    public static function get($name, $default = null)
    {
        return \Configuration::get($name, null, null, null, $default);
    }

    /**
     * Set option.
     *
     * @param string $name  option name.
     * @param string $value option value.
     *
     * @void
     */
    public static function set($name, $value)
    {
        \Configuration::updateValue($name, $value);
    }

    /**
     * Delete option.
     *
     * @param string $name option name.
     *
     * @void
     */
    public static function delete($name)
    {
        \Configuration::deleteByName($name);
    }
}

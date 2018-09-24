<?php
/**
 * Contains code for cookie util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

/**
 * Cookie util class.
 *
 * Helper to manage prestashop cookie.
 */
class CookieUtil
{

    /**
     * Get cookie.
     *
     * @return \Cookie cookie
     */
    private static function getCookie() {
        $boxtal = \Boxtal::getInstance();
        return $boxtal->getContext()->cookie;
    }

    /**
     * Get cookie variable value.
     *
     * @param string $key name of cookie variable.
     * @return mixed value
     */
    public static function get($key)
    {
        return self::getCookie()->$key;
    }

    /**
     * Set cookie variable value.
     *
     * @param string $key name of cookie variable.
     * @param string|array $value value of cookie variable.
     * @return mixed value
     */
    public static function set($key, $value)
    {
        $cookie = self::getCookie();
        $cookie->$key = $value;
    }

}

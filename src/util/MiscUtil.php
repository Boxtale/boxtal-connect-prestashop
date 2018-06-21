<?php
/**
 * Contains code for misc util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

/**
 * Misc util class.
 *
 * Misc helper.
 */
class MiscUtil
{

    /**
     * Is set or null.
     *
     * @param array  $array    array to test.
     * @param string $property property to test.
     *
     * @return string
     */
    public static function isSetOrNull($array, $property = null)
    {
        return isset($array[$property]) ? $array[$property] : null;
    }

    /**
     * Is set or null.
     *
     * @param array  $array    array to test.
     * @param string $property property to test.
     *
     * @return string
     */
    public static function notEmptyOrNull($array, $property = null)
    {
        $isSet = self::isSetOrNull($array, $property);

        return $isSet !== null && $isSet !== '' ? $isSet : null;
    }
}

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
     * @param object $object.
     * @param string $property.
     *
     * @return string
     */
    public static function isSetOrNull($object, $property = null)
    {
        return isset($object[$property]) ? $object[$property] : null;
    }

    /**
     * Is set or null.
     *
     * @param object $object.
     * @param string $property.
     *
     * @return string
     */
    public static function notEmptyOrNull($object, $property = null)
    {
        $isSet = self::isSetOrNull($object, $property);
        return $isSet !== null && $isSet !== '' ? $isSet : null;
    }
}

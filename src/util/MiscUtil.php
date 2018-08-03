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

    /**
     * Return base64 encoded value if not null.
     *
     * @param mixed $value value to be encoded.
     * @return mixed $value
     */
    public static function base64OrNull( $value ) {
        return null === $value ? null : base64_encode($value);
    }

    /**
     * Converts StdClass object to associative array.
     *
     * @param mixed $value value to be converted.
     * @return array $value
     */
    public static function convertStdClassToArray( $value ) {
        if (!is_object($value)) {
            return $value;
        }
        return json_decode(json_encode($value), true);
    }
}

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
     * Cast to float if not null.
     *
     * @param string  $string    string to cast.
     *
     * @return float
     */
    public static function toFloatOrNull($string)
    {
        return $string !== null ? (float)$string : null;
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
     * @param mixed $object value to be converted.
     * @return array $value
     */
    public static function convertStdClassToArray( $object ) {
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                if (is_array($value)) {
                    $object[$key] = self::convertStdClassToArray($value);
                }
                if ($value instanceof \stdClass) {
                    $object[$key] = self::convertStdClassToArray((array)$value);
                }
            }
        }
        if ($object instanceof \stdClass) {
            return self::convertStdClassToArray((array)$object);
        }
        return $object;
    }
}

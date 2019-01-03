<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Boxtal <api@boxtal.com>
 * @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for misc util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

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
     * @param array $array array to test
     * @param string $property property to test
     *
     * @return string
     */
    public static function isSetOrNull($array, $property = null)
    {
        return isset($array[$property]) ? $array[$property] : null;
    }

    /**
     * Property exists or null.
     *
     * @param object $object array to test
     * @param string $property property to test
     *
     * @return string
     */
    public static function propertyExistsOrNull($object, $property = null)
    {
        return property_exists($object, $property) ? $object->$property : null;
    }

    /**
     * Is set or null.
     *
     * @param array $array array to test
     * @param string $property property to test
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
     * @param string $string string to cast
     *
     * @return float
     */
    public static function toFloatOrNull($string)
    {
        return $string !== null ? (float) $string : null;
    }

    /**
     * Return base64 encoded value if not null.
     *
     * @param mixed $value value to be encoded
     *
     * @return mixed $value
     */
    public static function base64OrNull($value)
    {
        return null === $value ? null : base64_encode($value);
    }

    /**
     * Converts StdClass object to associative array.
     *
     * @param mixed $object value to be converted
     *
     * @return array $value
     */
    public static function convertStdClassToArray($object)
    {
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                if (is_array($value)) {
                    $object[$key] = self::convertStdClassToArray($value);
                }
                if ($value instanceof \stdClass) {
                    $object[$key] = self::convertStdClassToArray((array) $value);
                }
            }
        }
        if ($object instanceof \stdClass) {
            return self::convertStdClassToArray((array) $object);
        }

        return $object;
    }

    /**
     * Return date with W3C format if not null.
     *
     * @param string $date date to be formatted
     *
     * @return string
     */
    public static function dateW3Cformat($date)
    {
        $date = new \DateTime($date);

        return $date->format(\DateTime::W3C);
    }

    /**
     * Return first admin user
     *
     * @return array
     */
    public static function getFirstAdminUser()
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('employee', 'e');
        $sql->where('e.id_profile = 1');
        $sql->where('e.active = 1');
        $sql->orderBy('e.id_employee asc');
        $sql->limit('limit(0,1)');
        $user = \Db::getInstance()->executeS($sql);

        return isset($user[0]) ? $user[0] : null;
    }

    /**
     * Return first admin user email
     *
     * @return string
     */
    public static function getFirstAdminUserEmail()
    {
        $user = self::getFirstAdminUser();

        return isset($user['email']) ? $user['email'] : null;
    }
}

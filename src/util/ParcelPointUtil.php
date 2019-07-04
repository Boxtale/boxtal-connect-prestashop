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
 * Contains code for parcelpoint storage util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

class ParcelPointUtil
{

    private static function normalizeOpeningHours($openingHours) {
        $result = null;

        if ($openingHours !== null && is_array($openingHours)) {
            $result = array();
            foreach ($openingHours as $openingHour) {
                $validOpeningHours = property_exists($openingHour, 'weekday')
                    && property_exists($openingHour, 'openingPeriods')
                    && is_array($openingHour->openingPeriods);

                if ($validOpeningHours) {
                    $dayOpeningHours = new \stdClass();
                    $dayOpeningHours->weekday = $openingHour->weekday;
                    $dayOpeningHours->openingPeriods = array();
                    foreach ($openingHour->openingPeriods as $period) {
                        $open = property_exists($period, 'openingTime')
                            ? $period->openingTime
                            : (property_exists($period, 'open') ? $period->open : null);
                        $close = property_exists($period, 'closingTime')
                            ? $period->closingTime
                            : (property_exists($period, 'close') ? $period->close : null);

                        $hours = new \stdClass();
                        $hours->open = $open;
                        $hours->close = $close;
                        $dayOpeningHours->openingPeriods[] = $hours;
                    }
                    $result[] = $dayOpeningHours;
                }
            }
        }

        return $result;
    }

    /**
     * Create a new parcel point object
     *
     * @param string $network
     * @param string $code
     * @param string $name
     * @param string $address
     * @param string $zipcode
     * @param string $city
     * @param string $country
     * @param string $openingHours
     *
     * @return mixed point in standard format
     */
    public static function createParcelPoint($network, $code, $name, $address, $zipcode, $city, $country, $openingHours)
    {
        $point = null;
        if (null !== $network && null !== $code && null !== $name) {
            $point = new \stdClass();
            $point->network = $network;
            $point->code = $code;
            $point->name = $name;
            $point->address = $address;
            $point->zipcode = $zipcode;
            $point->city = $city;
            $point->country = $country;
            $point->openingHours = static::normalizeOpeningHours($openingHours);
        }

        return $point;
    }

    /**
     * Normalize the point format for retrocompatibility reasons
     *
     * default format   : format used globally in the module since 1.2.0
     * old order format : format used in order storage before 1.2.0
     * old cart format  : format used in cart storage before 1.2.0
     * api format       : format returned by boxtal api
     *
     * @param mixed $point in new or olf format
     *
     * @return mixed point in new format
     */
    public static function normalizePoint($point)
    {
        $result = null;

        if ($point !== null && $point !== false) {
            $hasNetwork = property_exists($point, 'network');
            $hasCode = property_exists($point, 'code');
            $hasName = property_exists($point, 'name');
            $hasAddress = property_exists($point, 'address');
            $hasZipcode = property_exists($point, 'zipcode');
            $hasCity = property_exists($point, 'city');
            $hasCountry = property_exists($point, 'country');
            $hasOpeningHours = property_exists($point, 'openingHours');
            $hasOpeningDays = property_exists($point, 'openingDays');
            $hasLocation = property_exists($point, 'location')
                && property_exists($point->location, 'street')
                && property_exists($point->location, 'zipCode')
                && property_exists($point->location, 'city')
                && property_exists($point->location, 'country');
            $hasParcelPoint = property_exists($point, 'parcelPoint')
                && property_exists($point->parcelPoint, 'network')
                && property_exists($point->parcelPoint, 'code')
                && property_exists($point->parcelPoint, 'name');

            $isDefaultFormat = $hasNetwork && $hasCode && $hasName && $hasAddress
                && $hasZipcode && $hasCity && $hasCountry && $hasOpeningHours && !$hasLocation;
            $isOldOrderFormat = $hasNetwork && $hasCode && $hasName && !$hasAddress
                && !$hasZipcode && !$hasCity && !$hasCountry && !$hasOpeningHours && !$hasLocation;
            $isOldCartFormat = $hasParcelPoint;
            $isApiFormat = $hasNetwork && $hasCode && $hasName && !$hasAddress
                && !$hasZipcode && !$hasCity && !$hasCountry && !$hasOpeningHours && $hasLocation && $hasOpeningDays;

            if ($isApiFormat) {
                $result = static::createParcelPoint(
                    $point->network,
                    $point->code,
                    $point->name,
                    $point->location->street,
                    $point->location->zipCode,
                    $point->location->city,
                    $point->location->country,
                    $point->openingDays
                );
            } elseif ($isDefaultFormat) {
                $result = $point;
            } elseif ($isOldOrderFormat) {
                $result = static::createParcelPoint($point->network, $point->code, $point->name, '', '', '', '', array());
            } elseif ($isOldCartFormat) {
                $sPoint = $point->parcelPoint;
                $result = static::createParcelPoint($sPoint->network, $sPoint->code, $sPoint->name, '', '', '', '', array());
            }
        }

        return $result;
    }

    /**
     * Set chosen parcel point.
     *
     * @param int $cartId cart id
     * @param int $carrierId shipping method id
     * @param mixed $point parcel point to save
     *
     */
    public static function setChosenPoint($cartId, $carrierId, $point)
    {
        $serializedPoint = null === $point ? null : serialize($point);
        CartStorageUtil::set($cartId, 'bxChosenParcelPoint' . $carrierId, $serializedPoint);
    }

    /**
     * Get chosen parcel point.
     *
     * @param int $cartId cart id
     * @param string $id shipping method id
     *
     * @return mixed
     */
    public static function getChosenPoint($cartId, $id)
    {
        $point = @unserialize(CartStorageUtil::get($cartId, 'bxChosenParcelPoint' . $id));
        return static::normalizePoint($point);
    }

    /**
     * Set order parcel point.
     *
     * @param int $orderId order id
     *
     * @return mixed
     */
    public static function setOrderParcelPoint($orderId, $point)
    {
        $serializedPoint = $point === null ? null : serialize($point);
        OrderStorageUtil::set($orderId, 'bxParcelPoint', $serializedPoint);
    }

    /**
     * Get order parcel point.
     *
     * @param int $orderId order id
     *
     * @return mixed
     */
    public static function getOrderParcelPoint($orderId)
    {
        $point = @unserialize(OrderStorageUtil::get($orderId, 'bxParcelPoint'));

        // retrocompatibility check
        if (false === $point) {
            $code = OrderStorageUtil::get($orderId, 'bxParcelPointCode');
            $network = OrderStorageUtil::get($orderId, 'bxParcelPointNetwork');
            if ($code !== null && $network !== null) {
                $point = static::createParcelPoint($network, $code, '', '', '', '', '');
            }
        }

        return static::normalizePoint($point);
    }
}

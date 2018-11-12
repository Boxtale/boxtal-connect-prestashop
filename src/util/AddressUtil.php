<?php
/**
 * Contains code for address util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

/**
 * Address util class.
 *
 * Helper to manage address.
 */
class AddressUtil
{

    /**
     * Convert prestashop address to boxtal address.
     *
     * @param \Address $address prestashop address.
     *
     * @return array converted address
     */
    public static function convert($address)
    {
        $convertedAddress = array(
            'street'   => trim(MiscUtil::propertyExistsOrNull($address, 'address1').' '.MiscUtil::propertyExistsOrNull($address, 'address2')),
            'city'     => trim(MiscUtil::propertyExistsOrNull($address, 'city')),
            'zipCode' => trim(MiscUtil::propertyExistsOrNull($address, 'postcode')),
            'country'  => self::getCountryIsoFromId(MiscUtil::propertyExistsOrNull($address, 'id_country')),
        );

        //phpcs:ignore
        if (null !== MiscUtil::propertyExistsOrNull($address, 'id_state') && 0 !== (int) $address->id_state) {
            //phpcs:ignore
            $convertedAddress['state'] = self::getStateIsoFromId((int) $address->id_state);
        }

        return $convertedAddress;
    }

    /**
     * Get country iso code from country id.
     *
     * @param int $countryId country id.
     *
     * @return string country iso code
     */
    public static function getCountryIsoFromId($countryId)
    {
        $country = new \Country($countryId);

        //phpcs:ignore
        return property_exists($country, 'iso_code') ? strtolower($country->iso_code) : null;
    }

    /**
     * Get country id from country iso code.
     *
     * @param string $countryIso country iso code.
     *
     * @return int country id
     */
    public static function getCountryIdFromIso($countryIso)
    {
        return \Country::getByIso($countryIso);
    }

    /**
     * Get state iso code from state id.
     *
     * @param int $stateId state id.
     *
     * @return string state iso code
     */
    public static function getStateIsoFromId($stateId)
    {
        $state = new \State($stateId);

        //phpcs:ignore
        return property_exists($state, 'iso_code') ? strtolower($state->iso_code) : null;
    }
}

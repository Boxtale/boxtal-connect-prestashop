<?php
/**
 * Contains code for address util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

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
     * @return array converted address
     */
    public static function convert($address)
    {
        return array(
            'street'   => trim(MiscUtil::propertyExistsOrNull($address, 'address1') . ' ' . MiscUtil::propertyExistsOrNull($address, 'address2')),
            'city'     => trim(MiscUtil::propertyExistsOrNull($address, 'city')),
            'postcode' => trim(MiscUtil::propertyExistsOrNull($address, 'postcode')),
            'country'  => strtolower( MiscUtil::propertyExistsOrNull($address, 'country') ),
        );
    }
}

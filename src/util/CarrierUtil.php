<?php
/**
 * Contains code for carrier util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

/**
 * Carrier util class.
 *
 * Helper to manage carriers.
 */
class CarrierUtil
{

    /**
     * Get carrier reference from id.
     *
     * @param int $carrierId carrier id
     *
     * @return int
     */
    public static function getReferenceFromId($carrierId)
    {
        $sql = new \DbQuery();
        $sql->select('c.id_reference');
        $sql->from('carrier', 'c');
        $sql->where('c.id_carrier = '.$carrierId);
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return null;
        }

        $row = array_shift($result);

        return (int) $row['id_reference'];
    }
}

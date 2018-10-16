<?php
/**
 * Contains code for shipping method util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;

/**
 * Shipping method util class.
 *
 * Helper to manage shipping methods.
 */
class ShippingMethodUtil
{

    /**
     * Get all parcel point operators selected in at least one shipping method.
     *
     * @return array $selectedParcelPointOperators.
     */
    public static function getSelectedParcelPointOperators()
    {
        $selectedParcelPointOperators = array();
        $shippingMethods = self::getShippingMethods();
        $parcelPointOperators = unserialize(ConfigurationUtil::get('BX_PP_OPERATORS'));
        if (!is_array($parcelPointOperators)) {
            return array();
        }

        foreach ((array) $shippingMethods as $shippingMethod) {
            if (isset($shippingMethod['parcel_point_operators'])) {
                $shippingMethodOperators = unserialize($shippingMethod['parcel_point_operators']);
                foreach ((array) $shippingMethodOperators as $shippingMethodOperator) {
                    if (!in_array($shippingMethodOperator, $selectedParcelPointOperators, true)) {
                        foreach ($parcelPointOperators as $parcelPointOperator) {
                            if ($shippingMethodOperator === $parcelPointOperator['code']) {
                                $selectedParcelPointOperators[] = $shippingMethodOperator;
                            }
                        }
                    }
                }
            }
        }

        return $selectedParcelPointOperators;
    }

    /**
     * Get parcel point operators associated with shipping methods.
     *
     * @return object shipping methods.
     */
    public static function getShippingMethods()
    {
        $sql = new \DbQuery();
        $sql->select('c.id_carrier, c.name, bc.parcel_point_networks');
        $sql->from('carrier', 'c');
        $sql->innerJoin('carrier_lang', 'cl', 'c.id_carrier = cl.id_carrier AND cl.id_shop = '.(int) ShopUtil::getCurrentShop().' AND cl.id_lang = '.(int) \Context::getContext()->language->id);
        $sql->leftJoin('bx_carrier', 'bc', 'c.id_carrier = bc.id_carrier');
        $sql->where('c.deleted = 0');

        return \Db::getInstance()->executeS($sql);
    }
}

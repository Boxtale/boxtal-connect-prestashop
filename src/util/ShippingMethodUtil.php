<?php
/**
 * Contains code for shipping method util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use boxtalconnect;

/**
 * Shipping method util class.
 *
 * Helper to manage shipping methods.
 */
class ShippingMethodUtil
{

    /**
     * Get all parcel point networks selected in at least one shipping method.
     *
     * @return array $selectedParcelPointNetworks.
     */
    public static function getAllSelectedParcelPointNetworks()
    {
        $selectedParcelPointNetworks = array();
        $shippingMethods = self::getShippingMethods();
        $parcelPointNetworks = unserialize(ConfigurationUtil::get('BX_PP_NETWORKS'));
        if (!is_array($parcelPointNetworks)) {
            return array();
        }

        foreach ((array) $shippingMethods as $shippingMethod) {
            if (isset($shippingMethod['parcel_point_networks'])) {
                $shippingMethodNetworks = unserialize($shippingMethod['parcel_point_networks']);
                foreach ((array) $shippingMethodNetworks as $shippingMethodNetwork) {
                    if (!in_array($shippingMethodNetwork, $selectedParcelPointNetworks, true)) {
                        foreach ($parcelPointNetworks as $parcelPointNetwork => $carrier) {
                            if ($shippingMethodNetwork === $parcelPointNetwork) {
                                $selectedParcelPointNetworks[] = $shippingMethodNetwork;
                            }
                        }
                    }
                }
            }
        }

        return $selectedParcelPointNetworks;
    }

    /**
     * Get parcel point networks associated with shipping methods.
     *
     * @param int   $carrierId           carrier id.
     * @param array $parcelPointNetworks array of parcel point network codes.
     *
     * @return object shipping methods.
     */
    public static function setSelectedParcelPointNetworks($carrierId, $parcelPointNetworks)
    {
        $data = array(
            'id_carrier' => $carrierId,
            'id_shop_group' => ShopUtil::$shopGroupId,
            'id_shop' => ShopUtil::$shopId,
            'parcel_point_networks' => pSQL(serialize($parcelPointNetworks)),
        );

        \Db::getInstance()->insert(
            'bx_carrier',
            $data,
            true,
            true,
            \Db::REPLACE
        );
    }

    /**
     * Get all parcel point networks selected in a shipping method.
     *
     * @param string $id shipping method id.
     *
     * @return array $selectedParcelPointNetworks.
     */
    public static function getSelectedParcelPointNetworks($id)
    {
        $selectedParcelPointNetworks = array();
        $shippingMethods = self::getShippingMethods();
        $parcelPointNetworks = unserialize(ConfigurationUtil::get('BX_PP_NETWORKS'));
        var_dump(ConfigurationUtil::get('BX_PP_NETWORKS'));
        if (!is_array($parcelPointNetworks)) {
            return array();
        }

        foreach ((array) $shippingMethods as $shippingMethod) {
            if (isset($shippingMethod['parcel_point_networks']) && (int) $shippingMethod['id_carrier'] === (int) $id) {
                $shippingMethodNetworks = unserialize($shippingMethod['parcel_point_networks']);
                foreach ((array) $shippingMethodNetworks as $shippingMethodNetwork) {
                    if (!in_array($shippingMethodNetwork, $selectedParcelPointNetworks, true)) {
                        foreach ($parcelPointNetworks as $parcelPointNetwork => $carrier) {
                            if ($shippingMethodNetwork === $parcelPointNetwork) {
                                $selectedParcelPointNetworks[] = $shippingMethodNetwork;
                            }
                        }
                    }
                }
            }
        }

        return $selectedParcelPointNetworks;
    }

    /**
     * Get parcel point networks associated with shipping methods.
     *
     * @return object shipping methods.
     */
    public static function getShippingMethods()
    {
        $sql = new \DbQuery();
        $sql->select('c.id_carrier, c.name, bc.parcel_point_networks');
        $sql->from('carrier', 'c');
        $sql->innerJoin('carrier_lang', 'cl', 'c.id_carrier = cl.id_carrier AND cl.id_lang = '.(int) \Context::getContext()->language->id);
        $sql->leftJoin('bx_carrier', 'bc', 'c.id_carrier = bc.id_carrier');

        if (null === ShopUtil::$shopGroupId) {
            $sql->where('bc.id_shop_group IS NULL');
        } else {
            $sql->where('bc.id_shop_group ='.ShopUtil::$shopGroupId);
        }

        if (null === ShopUtil::$shopId) {
            $sql->where('cl.id_shop IS NULL');
            $sql->where('bc.id_shop IS NULL');
        } else {
            $sql->where('cl.id_shop ='.ShopUtil::$shopId);
            $sql->where('bc.id_shop ='.ShopUtil::$shopId);
        }

        $sql->where('c.deleted = 0');

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Get clean carrier id.
     *
     * @param string $carrierId carrier id
     *
     * @return int
     */
    public static function getCleanId($carrierId)
    {
        return (int) $carrierId;
    }
}

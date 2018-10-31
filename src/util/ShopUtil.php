<?php
/**
 * Contains code for shop util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;

/**
 * Shop util class.
 *
 * Helper to manage shops.
 */
class ShopUtil
{

    /**
     * Get shop name.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return string shop name.
     */
    public static function getShopName($shopGroupId, $shopId)
    {

        $sql = new \DbQuery();
        $sql->select('s.name');
        $sql->from('shop', 's');
        $sql->where('s.id_shop="'.$shopId.'" AND s.id_shop_group="'.$shopGroupId.'"');
        $shop = \Db::getInstance()->executeS($sql);

        return isset($shop[0]['name']) ? $shop[0]['name'] : null;
    }

    /**
     * Get shop url.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return string shop url.
     */
    public static function getShopUrl($shopGroupId, $shopId)
    {
        $shopUrl = null;

        if (\Shop::isFeatureActive()) {
            $sql = new \DbQuery();
            $sql->select('s.domain, s.domain_ssl, s.physical_uri, s.virtual_uri');
            $sql->from('shop_url', 's');
            $sql->where('s.id_shop="'.$shopId.'"');
            $shop = \Db::getInstance()->executeS($sql);
            if (isset($shop[0]['domain'], $shop[0]['domain_ssl'], $shop[0]['physical_uri'], $shop[0]['virtual_uri'])) {
                $sslEnabled = ConfigurationUtil::get('PS_SSL_ENABLED', $shopGroupId, $shopId);
                $shopUrl = $sslEnabled ? 'https://'.$shop[0]['domain_ssl'] : 'http://'.$shop[0]['domain'];
                $shopUrl .= $shop[0]['physical_uri'].$shop[0]['virtual_uri'];
            }
        } else {
            $sslEnabled = ConfigurationUtil::get('PS_SSL_ENABLED', null, null);
            $shopUrl = $sslEnabled ? 'https://'.__PS_BASE_URI__ : 'http://'.__PS_BASE_URI__;
        }

        return $shopUrl;
    }

    /**
     * Get shops.
     *
     * @return array shops.
     */
    public static function getShops()
    {

        if (\Shop::isFeatureActive()) {
            $sql = new \DbQuery();
            $sql->select('s.id_shop, s.id_shop_group');
            $sql->from('shop', 's');
            $sql->where('s.active=1 AND s.deleted=0');

            $shops = \Db::getInstance()->executeS($sql);
        } else {
            $shops = array();
            $shops[] = array(
                'id_shop' => self::getCurrentShopId(),
                'id_shop_group' => self::getCurrentShopGroupId(),
            );
        }

        return $shops;
    }

    /**
     * Get shop context.
     *
     * @return array with shop id or shop group id, or null if 'all shops' context.
     */
    public static function getShopContext()
    {
        if (\Shop::isFeatureActive()) {
            $shopId = self::getCurrentShopId();
            $shopGroupId = self::getCurrentShopGroupId();
            if (0 === $shopGroupId && 0 === $shopId) {
                $shopContext = array('id_shop' => null, 'id_shop_group' => null, 'multistore' => 1);
            } else {
                $shopContext = array('id_shop' => (int) $shopId, 'id_shop_group' => (int) $shopGroupId, 'multistore' => 1);
            }
        } else {
            $shopContext = array('id_shop' => null, 'id_shop_group' => null, 'multistore' => 0);
        }

        return $shopContext;
    }

    /**
     * Get current shop id.
     *
     * @return int shop id.
     */
    private static function getCurrentShopId()
    {
        return (int) \Shop::getContextShopID(true);
    }

    /**
     * Get current shop group id.
     *
     * @return int shop id.
     */
    private static function getCurrentShopGroupId()
    {
        return (int) \Shop::getContextShopGroupID();
    }
}

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

    public static $shopId;

    public static $shopGroupId;

    public static $multistore;

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
        $sql = new \DbQuery();
        $sql->select('s.domain, s.domain_ssl, s.physical_uri, s.virtual_uri');
        $sql->from('shop_url', 's');
        if (null === $shopId) {
            $sql->where('s.id_shop IS NULL');
        } else {
            $sql->where('s.id_shop='.$shopId);
        }
        $shop = \Db::getInstance()->executeS($sql);
        if (isset($shop[0]['domain'], $shop[0]['domain_ssl'], $shop[0]['physical_uri'], $shop[0]['virtual_uri'])) {
            $sslEnabled = ConfigurationUtil::get('PS_SSL_ENABLED', $shopGroupId, $shopId);
            $shopUrl = $sslEnabled ? 'https://'.$shop[0]['domain_ssl'] : 'http://'.$shop[0]['domain'];
            $shopUrl .= $shop[0]['physical_uri'].$shop[0]['virtual_uri'];
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
        $sql = new \DbQuery();
        $sql->select('s.id_shop, s.id_shop_group');
        $sql->from('shop', 's');
        $sql->where('s.active=1 AND s.deleted=0');
        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Get shop context.
     *
     * @void
     */
    public static function getShopContext()
    {
        if (\Shop::isFeatureActive()) {
            self::$shopGroupId = self::getCurrentShopGroupId();
            self::$shopId = self::getCurrentShopId();
            self::$multistore = true;
        } else {
            self::$shopGroupId = self::getCurrentShopGroupId();
            self::$shopId = self::getCurrentShopId();
            self::$multistore = false;
        }
    }

    /**
     * Get current shop id.
     *
     * @return int shop id.
     */
    private static function getCurrentShopId()
    {
        return \Shop::getContextShopID();
    }

    /**
     * Get current shop group id.
     *
     * @return int shop id.
     */
    private static function getCurrentShopGroupId()
    {
        return \Shop::getContextShopGroupID();
    }
}

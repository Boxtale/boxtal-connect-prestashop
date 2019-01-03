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
 * Contains code for shop util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

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
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     *
     * @return string shop name
     */
    public static function getShopName($shopGroupId, $shopId)
    {
        $sql = new \DbQuery();
        $sql->select('s.name');
        $sql->from('shop', 's');
        $sql->where('s.id_shop="' . (int) $shopId . '" AND s.id_shop_group="' . (int) $shopGroupId . '"');
        $shop = \Db::getInstance()->executeS($sql);

        return isset($shop[0]['name']) ? $shop[0]['name'] : null;
    }

    /**
     * Get shop url.
     *
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     *
     * @return string shop url
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
            $sql->where('s.id_shop=' . (int) $shopId);
        }
        $shop = \Db::getInstance()->executeS($sql);
        if (isset($shop[0]['domain'], $shop[0]['domain_ssl'], $shop[0]['physical_uri'], $shop[0]['virtual_uri'])) {
            $sslEnabled = ConfigurationUtil::get('PS_SSL_ENABLED', $shopGroupId, $shopId);
            $shopUrl = $sslEnabled ? 'https://' . $shop[0]['domain_ssl'] : 'http://' . $shop[0]['domain'];
            $shopUrl .= $shop[0]['physical_uri'] . $shop[0]['virtual_uri'];
        }

        return $shopUrl;
    }

    /**
     * Get shops.
     *
     * @return array shops
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
     * @return int shop id
     */
    private static function getCurrentShopId()
    {
        return \Shop::getContextShopID();
    }

    /**
     * Get current shop group id.
     *
     * @return int shop id
     */
    private static function getCurrentShopGroupId()
    {
        return \Shop::getContextShopGroupID();
    }
}

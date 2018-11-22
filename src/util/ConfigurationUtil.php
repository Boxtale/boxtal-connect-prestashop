<?php
/**
 * 2007-2018 PrestaShop
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
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for configuration util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use BoxtalConnect;

/**
 * Configuration util class.
 *
 * Helper to manage configuration.
 */
class ConfigurationUtil
{
    /**
     * Get option.
     *
     * @param string $name option name
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     * @param mixed $default option default value
     *
     * @return string option value
     */
    public static function get($name, $shopGroupId = null, $shopId = null, $default = null)
    {
        if (null === $shopGroupId) {
            $shopGroupId = ShopUtil::$shopGroupId;
        }

        if (null === $shopId) {
            $shopId = ShopUtil::$shopId;
        }

        $value = \Configuration::get($name, null, $shopGroupId, $shopId, $default);

        return null !== $value && false !== $value && '' !== $value ? $value : null;
    }

    /**
     * Set option.
     *
     * @param string $name option name
     * @param string $value option value
     *
     * @void
     */
    public static function set($name, $value)
    {
        \Configuration::updateValue($name, $value, false, ShopUtil::$shopGroupId, ShopUtil::$shopId);
    }

    /**
     * Delete option. Do NOT delete value in configuration cache.
     *
     * @param string $name option name
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     *
     * @void
     */
    public static function delete($name, $shopGroupId, $shopId)
    {
        if (false === ShopUtil::$multistore) {
            self::deleteAllShops($name);

            return;
        }

        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configuration` WHERE name="' . $name . '" ';

        if (null === $shopId) {
            $sql .= 'AND id_shop IS NULL ';
        } else {
            $sql .= 'AND id_shop=' . $shopId . ' ';
        }

        if (null === $shopGroupId) {
            $sql .= 'AND id_shop_group IS NULL ';
        } else {
            $sql .= 'AND id_shop_group=' . $shopGroupId . ' ';
        }

        \Db::getInstance()->execute($sql);
    }

    /**
     * Delete option for all shops. Deletes value in cache as well.
     *
     * @param string $name option name
     *
     * @void
     */
    public static function deleteAllShops($name)
    {
        \Configuration::deleteByName($name);
    }

    /**
     * Parse configuration.
     *
     * @param object $body body
     *
     * @return bool
     */
    public static function parseConfiguration($body)
    {
        return self::parseParcelPointNetworks($body) && self::parseMapConfiguration($body)
            && self::parseTrackingConfiguration($body);
    }

    /**
     * Has configuration.
     *
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     *
     * @return bool
     */
    public static function hasConfiguration($shopGroupId, $shopId)
    {
        return null !== self::get('BX_MAP_BOOTSTRAP_URL', $shopGroupId, $shopId)
            && null !== self::get('BX_MAP_TOKEN_URL', $shopGroupId, $shopId)
            && null !== self::get('BX_MAP_LOGO_IMAGE_URL', $shopGroupId, $shopId)
            && null !== self::get('BX_MAP_LOGO_HREF_URL', $shopGroupId, $shopId)
            && null !== self::get('BX_PP_NETWORKS', $shopGroupId, $shopId)
            && null !== self::get('BX_TRACKING_URL_PATTERN', $shopGroupId, $shopId);
    }

    /**
     * Build onboarding link.
     *
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     *
     * @return string onboarding link
     */
    public static function getOnboardingLink($shopGroupId, $shopId)
    {
        $boxtalConnect = BoxtalConnect::getInstance();
        $url = $boxtalConnect->onboardingUrl;
        $email = MiscUtil::getFirstAdminUserEmail();
        $locale = \Language::getIsoById((int) $boxtalConnect->getContext()->cookie->id_lang);
        $shopUrl = ShopUtil::getShopUrl($shopGroupId, $shopId);

        $params = array(
            'acceptLanguage' => $locale,
            'email' => $email,
            'shopUrl' => $shopUrl,
            'shopType' => 'prestashop',
        );

        return $url . '?' . http_build_query($params);
    }

    /**
     * Get map logo href url.
     *
     * @return string map logo href url
     */
    public static function getMapLogoHrefUrl()
    {
        $url = self::get('BX_MAP_LOGO_HREF_URL');

        return $url;
    }

    /**
     * Get map logo image url.
     *
     * @return string map logo image url
     */
    public static function getMapLogoImageUrl()
    {
        $url = self::get('BX_MAP_LOGO_IMAGE_URL');

        return $url;
    }

    /**
     * Get tracking url pattern.
     *
     * @return string tracking url pattern
     */
    public static function getTrackingUrlPattern()
    {
        $url = self::get('BX_TRACKING_URL_PATTERN');

        return $url;
    }

    /**
     * Delete configuration.
     *
     * @void
     */
    public static function deleteConfiguration()
    {
        self::deleteAllShops('BX_ACCESS_KEY');
        self::deleteAllShops('BX_SECRET_KEY');
        self::deleteAllShops('BX_MAP_BOOTSTRAP_URL');
        self::deleteAllShops('BX_MAP_TOKEN_URL');
        self::deleteAllShops('BX_MAP_LOGO_IMAGE_URL');
        self::deleteAllShops('BX_MAP_LOGO_HREF_URL');
        self::deleteAllShops('BX_PP_NETWORKS');
        self::deleteAllShops('BX_PAIRING_UPDATE');
        self::deleteAllShops('BX_ORDER_SHIPPED');
        self::deleteAllShops('BX_ORDER_DELIVERED');
        self::deleteAllShops('BX_TRACKING_URL_PATTERN');
        NoticeController::removeAllNoticesForShop();
    }

    /**
     * Parse parcel point operators response.
     *
     * @param object $body body
     *
     * @return bool
     */
    private static function parseParcelPointNetworks($body)
    {
        $boxtalConnect = BoxtalConnect::getInstance();
        if (is_object($body) && property_exists($body, 'parcelPointNetworks')) {
            $storedNetworks = self::get('BX_PP_NETWORKS');
            if (is_array($storedNetworks)) {
                $removedNetworks = $storedNetworks;
                //phpcs:ignore
                foreach ($body->parcelPointNetworks as $newNetwork => $newNetworkCarriers) {
                    foreach ($storedNetworks as $oldNetwork => $oldNetworkCarriers) {
                        if ($newNetwork === $oldNetwork) {
                            unset($removedNetworks[$oldNetwork]);
                        }
                    }
                }

                if (count($removedNetworks) > 0) {
                    NoticeController::addNotice(
                        NoticeController::$custom,
                        ShopUtil::$shopGroupId,
                        ShopUtil::$shopId,
                        array(
                            'status' => 'warning',
                            'message' => $boxtalConnect->l('There\'s been a change in the parcel point network list, ' .
                                'we\'ve adapted your shipping method configuration. Please check that everything is in' .
                                ' order.'),
                        )
                    );
                }

                //phpcs:ignore
                $addedNetworks = $body->parcelPointNetworks;
                //phpcs:ignore
                foreach ($body->parcelPointNetworks as $newNetwork => $newNetworkCarriers) {
                    foreach ($storedNetworks as $oldNetwork => $oldNetworkCarriers) {
                        if ($newNetwork === $oldNetwork) {
                            unset($addedNetworks[$oldNetwork]);
                        }
                    }
                }
                if (count($addedNetworks) > 0) {
                    NoticeController::addNotice(
                        NoticeController::$custom,
                        ShopUtil::$shopGroupId,
                        ShopUtil::$shopId,
                        array(
                            'status' => 'info',
                            'message' => $boxtalConnect->l('There\'s been a change in the parcel point network list, ' .
                                'you can add the extra parcel point network(s) to your shipping method configuration.'),
                        )
                    );
                }
            }
            //phpcs:ignore
            self::set('BX_PP_NETWORKS', serialize(MiscUtil::convertStdClassToArray($body->parcelPointNetworks)));

            return true;
        }

        return false;
    }

    /**
     * Parse map configuration.
     *
     * @param object $body body
     *
     * @return bool
     */
    private static function parseMapConfiguration($body)
    {
        if (is_object($body) && property_exists($body, 'mapsBootstrapUrl')
            && property_exists($body, 'mapsTokenUrl')
            && property_exists($body, 'mapsLogoImageUrl')
            && property_exists($body, 'mapsLogoHrefUrl')) {
            //phpcs:ignore
            self::set('BX_MAP_BOOTSTRAP_URL', $body->mapsBootstrapUrl);
            //phpcs:ignore
            self::set('BX_MAP_TOKEN_URL', $body->mapsTokenUrl);
            //phpcs:ignore
            self::set('BX_MAP_LOGO_IMAGE_URL', $body->mapsLogoImageUrl);
            //phpcs:ignore
            self::set('BX_MAP_LOGO_HREF_URL', $body->mapsLogoHrefUrl);

            return true;
        }

        return false;
    }

    /**
     * Parse tracking configuration.
     *
     * @param object $body body
     *
     * @return bool
     */
    private static function parseTrackingConfiguration($body)
    {
        if (is_object($body) && property_exists($body, 'trackingUrlPattern')) {
            $storedTrackingUrlPattern = self::getTrackingUrlPattern();
            if (null !== $storedTrackingUrlPattern && $storedTrackingUrlPattern !== $body->trackingUrlPattern) {
                $boxtalConnect = BoxtalConnect::getInstance();
                NoticeController::addNotice(
                    NoticeController::$custom,
                    ShopUtil::$shopGroupId,
                    ShopUtil::$shopId,
                    array(
                        'status' => 'warning',
                        'message' => $boxtalConnect->l('The Boxtal tracking url has changed, you should change it in ' .
                            'your shipping methods as well. The new link is displayed on the Boxtal settings page.'),
                    )
                );
            }

            //phpcs:ignore
            self::set('BX_TRACKING_URL_PATTERN', $body->trackingUrlPattern);

            return true;
        }

        return false;
    }
}

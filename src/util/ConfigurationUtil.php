<?php
/**
 * Contains code for configuration util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use boxtalconnect;

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
     * @param string $name        option name.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     * @param mixed  $default     option default value.
     *
     * @return string option value.
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

        return null !== $value && false !== $value ? $value : null;
    }

    /**
     * Set option.
     *
     * @param string $name  option name.
     * @param string $value option value.
     *
     * @void
     */
    public static function set($name, $value)
    {
        \Configuration::updateValue($name, $value, false, ShopUtil::$shopGroupId, ShopUtil::$shopId);
    }

    /**
     * Delete option.
     *
     * @param string $name option name.
     *
     * @void
     */
    public static function delete($name)
    {
        $sql = 'DELETE FROM `'._DB_PREFIX_.'configuration` WHERE name="'.$name.'" ';
        $shopId = ShopUtil::$shopId;
        $shopGroupId = ShopUtil::$shopGroupId;

        if (null === $shopId) {
            $sql .= 'AND id_shop IS NULL ';
        } else {
            $sql .= 'AND id_shop='.$shopId.' ';
        }

        if (null === $shopGroupId) {
            $sql .= 'AND id_shop_group IS NULL ';
        } else {
            $sql .= 'AND id_shop_group='.$shopGroupId.' ';
        }

        \Db::getInstance()->execute($sql);
    }


    /**
     * Get configuration.
     *
     * @return boolean
     */
    public static function getConfiguration()
    {
        $lib     = new ApiClient(null, null);
        $locale = \Language::getIsoById((int) Boxtal::getInstance()->getContext()->cookie->id_lang);
        $headers = array(
            'Accept-Language' => $locale,
        );
        //phpcs:disable
        $response = $lib->restClient->request(
            RestClient::$GET,
            $lib->getApiUrl() . '/public/plugin/configuration',
            array(),
            $headers
        );
        //phpcs:enable

        if (! $response->isError()) {
            if (self::parseConfiguration($response->response)) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Parse configuration.
     *
     * @param object $body body.
     *
     * @return boolean
     */
    public static function parseConfiguration($body)
    {
        return self::parseParcelPointNetworks($body) && self::parseMapConfiguration($body) && self::parseTrackingConfiguration($body);
    }

    /**
     * Has configuration.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return boolean
     */
    public static function hasConfiguration($shopGroupId, $shopId)
    {
        return null !== self::get('BX_MAP_BOOTSTRAP_URL', $shopGroupId, $shopId) && null !== self::get('BX_MAP_TOKEN_URL', $shopGroupId, $shopId)
            && null !== self::get('BX_MAP_LOGO_IMAGE_URL', $shopGroupId, $shopId) && null !== self::get('BX_MAP_LOGO_HREF_URL', $shopGroupId, $shopId)
            && null !== self::get('BX_PP_NETWORKS', $shopGroupId, $shopId) && null !== self::get('BX_TRACKING_URL_PATTERN', $shopGroupId, $shopId);
    }

    /**
     * Build onboarding link.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return string onboarding link
     */
    public static function getOnboardingLink($shopGroupId, $shopId)
    {
        $boxtalconnect = \boxtalconnect::getInstance();
        $url    = $boxtalconnect->onboardingUrl;
        $email = MiscUtil::getFirstAdminUserEmail();
        $locale = \Language::getIsoById((int) $boxtalconnect->getContext()->cookie->id_lang);
        $shopUrl = ShopUtil::getShopUrl($shopGroupId, $shopId);

        $params       = array(
            'acceptLanguage' => $locale,
            'email'       => $email,
            'shopUrl'     => $shopUrl,
            'shopType' => 'prestashop',
        );

        return $url.'?'.http_build_query($params);
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

        return str_replace('%s', '@', $url);
    }

    /**
     * Delete configuration.
     *
     * @void
     */
    public static function deleteConfiguration()
    {
        self::delete('BX_ACCESS_KEY');
        self::delete('BX_SECRET_KEY');
        self::delete('BX_MAP_BOOTSTRAP_URL');
        self::delete('BX_MAP_TOKEN_URL');
        self::delete('BX_MAP_LOGO_IMAGE_URL');
        self::delete('BX_MAP_LOGO_HREF_URL');
        self::delete('BX_PP_NETWORKS');
        self::delete('BX_PAIRING_UPDATE');
        self::delete('BX_ORDER_SHIPPED');
        self::delete('BX_ORDER_DELIVERED');
        self::delete('BX_TRACKING_URL_PATTERN');
        NoticeController::removeAllNoticesForShop();
    }

    /**
     * Parse parcel point operators response.
     *
     * @param object $body body.
     *
     * @return boolean
     */
    private static function parseParcelPointNetworks($body)
    {
        $boxtalconnect = boxtalconnect::getInstance();
        if (is_object($body) && property_exists($body, 'parcelPointNetworks')) {
            $storedNetworks = self::get('BX_PP_NETWORKS');
            if (is_array($storedNetworks)) {
                $removedNetworks = $storedNetworks;
                //phpcs:ignore
                foreach ( $body->parcelPointNetworks as $newNetwork => $newNetworkCarriers) {
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
                            'status'  => 'warning',
                            'message' => $boxtalconnect->l('There\'s been a change in the parcel point network list, we\'ve adapted your shipping method configuration. Please check that everything is in order.'),
                        )
                    );
                }

                //phpcs:ignore
                $addedNetworks = $body->parcelPointNetworks;
                //phpcs:ignore
                foreach ( $body->parcelPointNetworks as $newNetwork => $newNetworkCarriers ) {
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
                            'status'  => 'info',
                            'message' => $boxtalconnect->l('There\'s been a change in the parcel point network list, you can add the extra parcel point network(s) to your shipping method configuration.'),
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
     * @param object $body body.
     *
     * @return boolean
     */
    private static function parseMapConfiguration($body)
    {
        if (is_object($body) && property_exists($body, 'mapsBootstrapUrl') && property_exists($body, 'mapsTokenUrl')
            && property_exists($body, 'mapsLogoImageUrl') && property_exists($body, 'mapsLogoHrefUrl')) {
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
     * @param object $body body.
     *
     * @return boolean
     */
    private static function parseTrackingConfiguration($body)
    {
        if (is_object($body) && property_exists($body, 'trackingUrlPattern')) {
            //phpcs:ignore
            self::set('BX_TRACKING_URL_PATTERN', $body->trackingUrlPattern);

            return true;
        }

        return false;
    }
}

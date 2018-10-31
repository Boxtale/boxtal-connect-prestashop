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
    public static function get($name, $shopGroupId, $shopId, $default = null)
    {
        $value = \Configuration::get($name, null, $shopGroupId, $shopId, $default);

        return null !== $value && false !== $value ? $value : null;
    }

    /**
     * Set option.
     *
     * @param string $name        option name.
     * @param string $value       option value.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     *
     * @void
     */
    public static function set($name, $value, $shopGroupId, $shopId)
    {
        \Configuration::updateValue($name, $value, false, $shopGroupId, $shopId);
    }

    /**
     * Delete option.
     *
     * @param string $name        option name.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     *
     * @void
     */
    public static function delete($name, $shopGroupId, $shopId)
    {
        \DB::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'configuration` WHERE name="'.$name.'" AND id_shop='.$shopId.' AND id_shop_group='.$shopGroupId.';'
        );
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
        return self::parseParcelPointNetworks($body) && self::parseMapConfiguration($body);
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
        return null !== self::get('BX_MAP_BOOTSTRAP_URL', $shopGroupId, $shopId) && null !== self::get('BX_MAP_TOKEN_URL', $shopGroupId, $shopId) && null !== self::get('BX_PP_NETWORKS', $shopGroupId, $shopId);
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
        $boxtalConnect = \boxtalconnect::getInstance();
        $url    = $boxtalConnect->onboardingUrl;
        $sql = new \DbQuery();
        $sql->select('e.email');
        $sql->from('employee', 'e');
        $sql->where('e.id_profile = 1');
        $sql->orderBy('e.id_employee asc');
        $sql->limit('limit(0,1)');
        $adminUser = \Db::getInstance()->executeS($sql)[0];
        $locale = \Language::getIsoById((int) \boxtalconnect::getInstance()->getContext()->cookie->id_lang);
        $shopUrl = ShopUtil::getShopUrl($shopGroupId, $shopId);

        $params       = array(
            'acceptLanguage' => $locale,
            'email'       => $adminUser['email'],
            'shopUrl'     => $shopUrl,
            'shopType' => 'prestashop',
        );

        return $url.'?'.http_build_query($params);
    }

    /**
     * Get map logo href url.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return string map logo href url
     */
    public static function getMapLogoHrefUrl($shopGroupId, $shopId)
    {
        $url = self::get('BX_MAP_LOGO_HREF_URL', $shopGroupId, $shopId);

        return $url;
    }

    /**
     * Get map logo image url.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return string map logo image url
     */
    public static function getMapLogoImageUrl($shopGroupId, $shopId)
    {
        $url = self::get('BX_MAP_LOGO_IMAGE_URL', $shopGroupId, $shopId);

        return $url;
    }

    /**
     * Delete configuration.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @void
     */
    public static function deleteConfiguration($shopGroupId, $shopId)
    {
        self::delete('BX_ACCESS_KEY', $shopGroupId, $shopId);
        self::delete('BX_SECRET_KEY', $shopGroupId, $shopId);
        self::delete('BX_MAP_BOOTSTRAP_URL', $shopGroupId, $shopId);
        self::delete('BX_MAP_TOKEN_URL', $shopGroupId, $shopId);
        self::delete('BX_MAP_LOGO_IMAGE_URL', $shopGroupId, $shopId);
        self::delete('BX_MAP_LOGO_HREF_URL', $shopGroupId, $shopId);
        self::delete('BX_PP_NETWORKS', $shopGroupId, $shopId);
        self::delete('BX_TRACKING_EVENT', $shopGroupId, $shopId);
        self::delete('BX_PAIRING_UPDATE', $shopGroupId, $shopId);
        self::delete('BX_ORDER_SHIPPED', $shopGroupId, $shopId);
        self::delete('BX_ORDER_DELIVERED', $shopGroupId, $shopId);
        NoticeController::removeAllNoticesForShop($shopGroupId, $shopId);
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
            $storedNetworks = self::get('BX_PP_NETWORKS', $boxtalconnect->shopGroupId, $boxtalconnect->shopId);
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
                        $boxtalconnect->shopGroupId,
                        $boxtalconnect->shopId,
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
                        $boxtalconnect->shopGroupId,
                        $boxtalconnect->shopId,
                        array(
                            'status'  => 'info',
                            'message' => $boxtalconnect->l('There\'s been a change in the parcel point network list, you can add the extra parcel point network(s) to your shipping method configuration.'),
                        )
                    );
                }
            }
            //phpcs:ignore
            self::set('BX_PP_NETWORKS', serialize(MiscUtil::convertStdClassToArray($body->parcelPointNetworks)), $boxtalconnect->shopGroupId, $boxtalconnect->shopId);

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
            $boxtalconnect = boxtalconnect::getInstance();

            //phpcs:ignore
            self::set('BX_MAP_BOOTSTRAP_URL', $body->mapsBootstrapUrl, $boxtalconnect->shopGroupId, $boxtalconnect->shopId);
            //phpcs:ignore
            self::set('BX_MAP_TOKEN_URL', $body->mapsTokenUrl, $boxtalconnect->shopGroupId, $boxtalconnect->shopId);
            //phpcs:ignore
            self::set('BX_MAP_LOGO_IMAGE_URL', $body->mapsLogoImageUrl, $boxtalconnect->shopGroupId, $boxtalconnect->shopId);
            //phpcs:ignore
            self::set('BX_MAP_LOGO_HREF_URL', $body->mapsLogoHrefUrl, $boxtalconnect->shopGroupId, $boxtalconnect->shopId);

            return true;
        }

        return false;
    }
}

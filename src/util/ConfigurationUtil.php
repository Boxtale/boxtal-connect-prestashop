<?php
/**
 * Contains code for configuration util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;

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
     * @param string $name    option name.
     * @param mixed  $default option default value.
     *
     * @return string option value.
     */
    public static function get($name, $default = null)
    {
        $value = \Configuration::get($name, null, null, null, $default);

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
        \Configuration::updateValue($name, $value);
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
        \Configuration::deleteByName($name);
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
     * @return boolean
     */
    public static function hasConfiguration()
    {
        return null !== self::get('BX_MAP_BOOTSTRAP_URL') && null !== self::get('BX_MAP_TOKEN_URL') && null !== self::get('BX_PP_NETWORKS');
    }

    /**
     * Build onboarding link.
     *
     * @return string onboarding link
     */
    public static function getOnboardingLink()
    {
        $boxtalConnect = \BoxtalConnect::getInstance();
        $url    = $boxtalConnect->onboardingUrl;
        $sql = new \DbQuery();
        $sql->select('e.email');
        $sql->from('employee', 'e');
        $sql->where('e.id_profile = 1');
        $sql->orderBy('e.id_employee asc');
        $sql->limit('limit(0,1)');
        $adminUser = \Db::getInstance()->executeS($sql)[0];
        $locale = \Language::getIsoById((int) \BoxtalConnect::getInstance()->getContext()->cookie->id_lang);

        $params       = array(
            'acceptLanguage' => $locale,
            'email'       => $adminUser['email'],
            'shopUrl'     => \Tools::getHttpHost(true).__PS_BASE_URI__,
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
        self::delete('BX_TRACKING_EVENT');
        self::delete('BX_PAIRING_UPDATE');
        self::delete('BX_ORDER_SHIPPED');
        self::delete('BX_ORDER_DELIVERED');
        NoticeController::removeAllNotices();
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
                        array(
                            'status'  => 'warning',
                            'message' => boxtalconnect::getInstance()->l('There\'s been a change in Boxtal\'s parcel point network list, we\'ve adapted your shipping method configuration. Please check that everything is in order.'),
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
                        array(
                            'status'  => 'info',
                            'message' => boxtalconnect::getInstance()->l('There\'s been a change in Boxtal\'s parcel point network list, you can add the extra parcel point network(s) to your shipping method configuration.'),
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
}

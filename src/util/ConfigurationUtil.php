<?php
/**
 * Contains code for configuration util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use Boxtal;
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
        return self::parseParcelPointOperators($body) && self::parseMapConfiguration($body);
    }

    /**
     * Has configuration.
     *
     * @return boolean
     */
    public static function hasConfiguration()
    {
        return null !== self::get('BX_MAP_BOOTSTRAP_URL') && null !== self::get('BX_MAP_TOKEN_URL') && null !== self::get('BX_PP_OPERATORS');
    }

    /**
     * Build onboarding link.
     *
     * @return string onboarding link
     */
    public static function getOnboardingLink()
    {
        $boxtal = \BoxtalConnect::getInstance();
        $url    = $boxtal->onboardingUrl;
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
        self::delete('BX_PP_OPERATORS');
        self::delete('BX_TRACKING_EVENT');
        self::delete('BX_PAIRING_UPDATE');
        NoticeController::removeAllNotices();
    }

    /**
     * Parse parcel point operators response.
     *
     * @param object $body body.
     *
     * @return boolean
     */
    private static function parseParcelPointOperators($body)
    {
        if (is_object($body) && property_exists($body, 'parcelPointOperators')) {
            $storedOperators = self::get('BX_PP_OPERATORS');
            if (is_array($storedOperators)) {
                $removedOperators = $storedOperators;
                //phpcs:ignore
                foreach ( $body->parcelPointOperators as $newOperator ) {
                    foreach ($storedOperators as $key => $oldOperator) {
                        if ($newOperator->code === $oldOperator->code) {
                            unset($removedOperators[$key]);
                        }
                    }
                }

                if (count($removedOperators) > 0) {
                    NoticeController::addNotice(
                        NoticeController::$custom,
                        array(
                            'status'  => 'warning',
                            'message' => Boxtal::getInstance()->l('There\'s been a change in Boxtal\'s parcel point operator list, we\'ve adapted your shipping method configuration. Please check that everything is in order.'),
                        )
                    );
                }

                //phpcs:ignore
                $addedOperators = $body->parcelPointOperators;
                //phpcs:ignore
                foreach ( $body->parcelPointOperators as $newOperator ) {
                    foreach ($storedOperators as $key => $oldOperator) {
                        if ($newOperator->code === $oldOperator->code) {
                            unset($addedOperators[$key]);
                        }
                    }
                }
                if (count($addedOperators) > 0) {
                    NoticeController::addNotice(
                        NoticeController::$custom,
                        array(
                            'status'  => 'info',
                            'message' => Boxtal::getInstance()->l('There\'s been a change in Boxtal\'s parcel point operator list, you can add the extra parcel point operator(s) to your shipping method configuration.'),
                        )
                    );
                }
            }
            //phpcs:ignore
            self::set('BX_PP_OPERATORS', serialize(MiscUtil::convertStdClassToArray($body->parcelPointOperators)));

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
        if (is_object($body) && property_exists($body, 'mapsBootstrapUrl') && property_exists($body, 'mapsTokenUrl')) {
            //phpcs:ignore
            self::set('BX_MAP_BOOTSTRAP_URL', $body->mapsBootstrapUrl);
            //phpcs:ignore
            self::set('BX_MAP_TOKEN_URL', $body->mapsTokenUrl);

            return true;
        }

        return false;
    }
}

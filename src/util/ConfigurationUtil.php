<?php
/**
 * Contains code for configuration util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

use Boxtal;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;

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
        return \Configuration::get($name, null, null, null, $default);
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
    public static function getConfiguration() {
        $lib     = new ApiClient( null, null );
        $locale = \Language::getIsoById((int)Boxtal::getInstance()->getContext()->cookie->id_lang);
        $headers = array(
            'Accept-Language' => $locale
        );
        //phpcs:disable
        $response = $lib->restClient->request(
            RestClient::$GET,
            $lib->getApiUrl() . '/public/plugin/configuration',
            array(),
            $headers
        );
        //phpcs:enable

        if ( ! $response->isError() ) {
            if ( self::parseConfiguration( $response->response ) ) {
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
     * @return boolean
     */
    public static function parseConfiguration( $body ) {
        if ( is_object( $body ) && property_exists( $body, 'mapsEndpointUrl' ) && property_exists( $body, 'mapsTokenUrl' )
            && property_exists( $body, 'signupPageUrl' ) && property_exists( $body, 'parcelPointOperators' ) ) {
            //phpcs:ignore
            self::set('BX_MAP_URL', $body->mapsEndpointUrl);
            //phpcs:ignore
            self::set('BX_TOKEN_URL', $body->mapsTokenUrl);
            //phpcs:ignore
            self::set('BX_SIGNUP_URL', $body->signupPageUrl);

            $storedOperators = self::get( 'BX_PP_OPERATORS' );
            if ( is_array( $storedOperators ) ) {
                $removedOperators = $storedOperators;
                //phpcs:ignore
                foreach ( $body->parcelPointOperators as $newOperator ) {
                    foreach ( $storedOperators as $key => $oldOperator ) {
                        if ( $newOperator->code === $oldOperator->code ) {
                            unset( $removedOperators[ $key ] );
                        }
                    }
                }

                if ( count( $removedOperators ) > 0 ) {
                    NoticeController::addNotice(
                        NoticeController::$custom, array(
                            'status'  => 'warning',
                            'message' => Boxtal::getInstance()->l( 'There\'s been a change in Boxtal parcel point operator list, we\'ve adapted your shipping method configuration. Please check that everything is in order.' ),
                        )
                    );
                }

                //phpcs:ignore
                $addedOperators = $body->parcelPointOperators;
                //phpcs:ignore
                foreach ( $body->parcelPointOperators as $newOperator ) {
                    foreach ( $storedOperators as $key => $oldOperator ) {
                        if ( $newOperator->code === $oldOperator->code ) {
                            unset( $addedOperators[ $key ] );
                        }
                    }
                }
                if ( count( $addedOperators ) > 0 ) {
                    NoticeController::addNotice(
                        NoticeController::$custom, array(
                            'status'  => 'info',
                            'message' => Boxtal::getInstance()->l( 'There\'s been a change in Boxtal parcel point operator list, you can add the extra parcel point operator(s) to your shipping method configuration.' ),
                        )
                    );
                }
            }
            //phpcs:ignore
            self::set('BX_PP_OPERATORS', MiscUtil::convertStdClassToArray($body->parcelPointOperators));
            return true;
        }
        return false;
    }
}

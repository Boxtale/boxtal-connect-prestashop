<?php
/**
 * Contains code for the parcel point controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Front;

use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalConnectPrestashop\Util\AddressUtil;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\CookieUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;

/**
 * Parcel point controller class.
 *
 * @class       ParcelPointController
 *
 */
class ParcelPointController
{

    /**
     * Controllers for which parcel point link can appear.
     *
     * @var array
     */
    private static $parcelPointControllers = array(
        'OrderOpcController',
        'BoxtalAjaxModuleFrontController',
        'OrderController',
    );



    /**
     * Add scripts.
     *
     * @return string html
     */
    public static function addScripts()
    {
        $boxtal = \BoxtalConnect::getInstance();
        $translation = array(
            'error' => array(
                'carrierNotFound' => $boxtal->l('Unable to find carrier'),
                'addressNotFound' => $boxtal->l('Could not find address'),
                'mapServerError'  => $boxtal->l('Could not connect to map server'),
            ),
            'text'  => array(
                'openingHours'        => $boxtal->l('Opening hours'),
                'chooseParcelPoint'   => $boxtal->l('Choose this parcel point'),
                'yourAddress'         => $boxtal->l('Your address:'),
                'closeMap'            => $boxtal->l('Close map'),
                'selectedParcelPoint' => $boxtal->l('Your parcel point:'),
            ),
            'day'   => array(
                'MONDAY'    => $boxtal->l('monday'),
                'TUESDAY'   => $boxtal->l('tuesday'),
                'WEDNESDAY' => $boxtal->l('wednesday'),
                'THURSDAY'  => $boxtal->l('thursday'),
                'FRIDAY'    => $boxtal->l('friday'),
                'SATURDAY'  => $boxtal->l('saturday'),
                'SUNDAY'    => $boxtal->l('sunday'),
            ),
        );
        $smarty = $boxtal->getSmarty();
        $smarty->assign('translation', \Tools::jsonEncode($translation));
        $smarty->assign('mapUrl', self::getMapUrl());

        $controller = $boxtal->getCurrentController();
        if (method_exists($controller, 'registerJavascript')) {
            $controller->registerJavascript(
                'bx-mapbox-gl',
                'modules/'.$boxtal->name.'/views/js/mapbox-gl.min.js',
                array('priority' => 100, 'server' => 'local')
            );
            $controller->registerStylesheet(
                'bx-mapbox-gl',
                'modules/'.$boxtal->name.'/views/css/mapbox-gl.css',
                array('priority' => 100, 'server' => 'local')
            );
            $controller->registerJavascript(
                'bx-parcel-point',
                'modules/'.$boxtal->name.'/views/js/parcel-point.min.js',
                array('priority' => 100, 'server' => 'local')
            );
        } else {
            $controller->addJs(_MODULE_DIR_.'/'.$boxtal->name.'/views/js/mapbox-gl.min.js');
            $controller->addCss(_MODULE_DIR_.'/'.$boxtal->name.'/views/css/mapbox-gl.css', 'all');
            $controller->addJs(_MODULE_DIR_.'/'.$boxtal->name.'/views/js/parcel-point.min.js');
        }

        return $boxtal->displayTemplate('front/shipping-method/header.tpl');
    }

    /**
     * Add point info.
     *
     * @param array $params cart info
     *
     * @return string html
     */
    public static function initPoints($params)
    {
        CookieUtil::set('bxParcelPoints', null);

        if (!isset($params['cart'])) {
            return null;
        }
        $cart = $params['cart'];
        //phpcs:ignore
        $address = new \Address((int) $cart->id_address_delivery);

        $parcelPointOperators = ShippingMethodUtil::getSelectedParcelPointOperators();
        if (!empty($parcelPointOperators)) {
            $lib      = new ApiClient(AuthUtil::getAccessKey(), AuthUtil::getSecretKey());
            $response = $lib->getParcelPoints(AddressUtil::convert($address), $parcelPointOperators);
            if (! $response->isError() && property_exists($response->response, 'parcelPoints') && is_array($response->response->parcelPoints) && count($response->response->parcelPoints) > 0) {
                $json = \Tools::jsonEncode($response->response);
                CookieUtil::set('bxParcelPoints', $json);
                $boxtal = \BoxtalConnect::getInstance();
                $smarty = $boxtal->getSmarty();
                $smarty->assign('bxParcelPoints', $json);

                return $boxtal->displayTemplate('front/shipping-method/parcelPoint.tpl');
            }
        }

        return null;
    }

    /**
     * Get map url.
     *
     * @return string
     */
    public static function getMapUrl()
    {
        $token = AuthUtil::getMapsToken();
        if (null !== $token) {
            return str_replace('${access_token}', $token, ConfigurationUtil::get('BX_MAP_BOOTSTRAP_URL'));
        }

        return null;
    }

    /**
     * Is checkout page.
     *
     * @return boolean
     */
    private static function isCheckoutPage()
    {
        $boxtal = \BoxtalConnect::getInstance();
        $controller = $boxtal->getCurrentController();
        $controllerClass = get_class($controller);
        $psOrderProcessType = (int) ConfigurationUtil::get('PS_ORDER_PROCESS_TYPE');

        if (1 === $psOrderProcessType && in_array($controllerClass, self::$parcelPointControllers, true)) {
            return true;
        }

        $step = (int) \Tools::getValue('step');

        if (0 === $psOrderProcessType && 2 === $step) {
            return true;
        }

        if (0 === $psOrderProcessType && 0 === $step && in_array($controllerClass, self::$parcelPointControllers, true)) {
            return true;
        }

        return false;
    }
}

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
        $boxtalConnect = \BoxtalConnect::getInstance();
        $translation = array(
            'error' => array(
                'carrierNotFound' => $boxtalConnect->l('Unable to find carrier'),
                'addressNotFound' => $boxtalConnect->l('Could not find address'),
                'mapServerError'  => $boxtalConnect->l('Could not connect to map server'),
            ),
            'text'  => array(
                'openingHours'        => $boxtalConnect->l('Opening hours'),
                'chooseParcelPoint'   => $boxtalConnect->l('Choose this parcel point'),
                'yourAddress'         => $boxtalConnect->l('Your address:'),
                'closeMap'            => $boxtalConnect->l('Close map'),
                'selectedParcelPoint' => $boxtalConnect->l('Your parcel point:'),
            ),
            'day'   => array(
                'MONDAY'    => $boxtalConnect->l('monday'),
                'TUESDAY'   => $boxtalConnect->l('tuesday'),
                'WEDNESDAY' => $boxtalConnect->l('wednesday'),
                'THURSDAY'  => $boxtalConnect->l('thursday'),
                'FRIDAY'    => $boxtalConnect->l('friday'),
                'SATURDAY'  => $boxtalConnect->l('saturday'),
                'SUNDAY'    => $boxtalConnect->l('sunday'),
            ),
        );
        $smarty = $boxtalConnect->getSmarty();
        $smarty->assign('translation', \Tools::jsonEncode($translation));
        $smarty->assign('mapUrl', self::getMapUrl());

        $controller = $boxtalConnect->getCurrentController();
        if (method_exists($controller, 'registerJavascript')) {
            $controller->registerJavascript(
                'bx-mapbox-gl',
                'modules/'.$boxtalConnect->name.'/views/js/mapbox-gl.min.js',
                array('priority' => 100, 'server' => 'local')
            );
            $controller->registerStylesheet(
                'bx-mapbox-gl',
                'modules/'.$boxtalConnect->name.'/views/css/mapbox-gl.css',
                array('priority' => 100, 'server' => 'local')
            );
            $controller->registerJavascript(
                'bx-parcel-point',
                'modules/'.$boxtalConnect->name.'/views/js/parcel-point.min.js',
                array('priority' => 100, 'server' => 'local')
            );
        } else {
            $controller->addJs(_MODULE_DIR_.'/'.$boxtalConnect->name.'/views/js/mapbox-gl.min.js');
            $controller->addCss(_MODULE_DIR_.'/'.$boxtalConnect->name.'/views/css/mapbox-gl.css', 'all');
            $controller->addJs(_MODULE_DIR_.'/'.$boxtalConnect->name.'/views/js/parcel-point.min.js');
        }

        return $boxtalConnect->displayTemplate('front/shipping-method/header.tpl');
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
                $boxtalConnect = \BoxtalConnect::getInstance();
                $smarty = $boxtalConnect->getSmarty();
                $smarty->assign('bxParcelPoints', $json);

                return $boxtalConnect->displayTemplate('front/shipping-method/parcelPoint.tpl');
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
        $boxtalConnect = \BoxtalConnect::getInstance();
        $controller = $boxtalConnect->getCurrentController();
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

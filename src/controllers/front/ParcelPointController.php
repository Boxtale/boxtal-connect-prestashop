<?php
/**
 * Contains code for the parcel point controller class.
 */

namespace Boxtal\BoxtalPrestashop\Controllers\Front;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPrestashop\Util\AddressUtil;
use Boxtal\BoxtalPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalPrestashop\Util\CookieUtil;
use Boxtal\BoxtalPrestashop\Util\ShippingMethodUtil;


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
        'OrderController'
    );

    /**
     * Add scripts.
     *
     * @void
     */
    public static function addScripts()
    {
        if (!self::isCheckoutPage()) {
            return;
        }
        $boxtal = \Boxtal::getInstance();
        $translation = array(
            'error' => array(
                'carrierNotFound' => $boxtal->l( 'Unable to find carrier' ),
                'addressNotFound' => $boxtal->l( 'Could not find address' ),
                'mapServerError'  => $boxtal->l( 'Could not connect to map server' ),
            ),
            'text'  => array(
                'openingHours'        => $boxtal->l( 'Opening hours' ),
                'chooseParcelPoint'   => $boxtal->l( 'Choose this parcel point' ),
                'yourAddress'         => $boxtal->l( 'Your address:' ),
                'closeMap'            => $boxtal->l( 'Close map' ),
                'selectedParcelPoint' => $boxtal->l( 'Your parcel point:' ),
            ),
            'day'   => array(
                'MONDAY'    => $boxtal->l( 'monday' ),
                'TUESDAY'   => $boxtal->l( 'tuesday' ),
                'WEDNESDAY' => $boxtal->l( 'wednesday' ),
                'THURSDAY'  => $boxtal->l( 'thursday' ),
                'FRIDAY'    => $boxtal->l( 'friday' ),
                'SATURDAY'  => $boxtal->l( 'saturday' ),
                'SUNDAY'    => $boxtal->l( 'sunday' ),
            ),
        );
        $smarty = $boxtal->getSmarty();
        $smarty->assign('translation', \Tools::jsonEncode($translation));
        $smarty->assign('mapUrl', \Tools::jsonEncode($translation));

        $controller = $boxtal->getCurrentController();
        if (method_exists($controller, 'registerJavascript')) {
            $controller->registerJavascript(
                'bx-mapbox-gl',
                'modules/'.$boxtal->name.'/views/js/mapbox-gl.min.js',
                array('priority' => 100, 'server' => 'local')
            );
            $controller->registerJavascript(
                'bx-parcel-point',
                'modules/'.$boxtal->name.'/views/js/parcel-point.min.js',
                array('priority' => 100, 'server' => 'local')
            );
        } else {
            $controller->addJs(_MODULE_DIR_ . '/' . $boxtal->name . '/views/js/mapbox-gl.min.js');
            $controller->addJs(_MODULE_DIR_ . '/' . $boxtal->name . '/views/js/parcel-point.min.js');
        }

        return $boxtal->displayTemplate('front/shipping-method/header.tpl');
    }

    /**
     * Add point info.
     *
     * @return string html
     */
    public static function initPoints($params)
    {
        CookieUtil::set('bxParcelPoints', null);

        if (!self::isCheckoutPage()) {
            return null;
        }

        if (!isset($params['cart'])) {
            return null;
        }
        $cart = $params['cart'];
        $address = new \Address((int)$cart->id_address_delivery);

        $parcelPointOperators = ShippingMethodUtil::getSelectedParcelPointOperators();
        if (!empty($parcelPointOperators)) {
            $lib      = new ApiClient( AuthUtil::getAccessKey(), AuthUtil::getSecretKey() );
            $response = $lib->getParcelPoints( AddressUtil::convert($address), $parcelPointOperators );
            if ( ! $response->isError() && property_exists( $response->response, 'parcelPoints' ) && is_array( $response->response->parcelPoints ) && count( $response->response->parcelPoints ) > 0 ) {
                $json = \Tools::jsonEncode($response->response);
                CookieUtil::set('bxParcelPoints', $json);
                $boxtal = \Boxtal::getInstance();
                $smarty = $boxtal->getSmarty();
                $smarty->assign('bxParcelPoints', $json);
                return $boxtal->displayTemplate('front/shipping-method/parcelPoint.tpl');
            }
        }

        return null;
    }

    /**
     * Is checkout page.
     *
     * @return boolean
     */
    private static function isCheckoutPage() {
        $boxtal = \Boxtal::getInstance();
        $controller = $boxtal->getCurrentController();
        $controllerClass = get_class($controller);
        $psOrderProcessType = (int)ConfigurationUtil::get('PS_ORDER_PROCESS_TYPE');
        $step = (int)\Tools::getValue('step');

        if (1 === $psOrderProcessType && in_array($controllerClass, self::$parcelPointControllers, true)) {
            return true;
        }

        if (0 === $psOrderProcessType && 2 === $step) {
            return true;
        }

        if (0 === $psOrderProcessType && 0 === $step && in_array($controllerClass, self::$parcelPointControllers, true)) {
            return true;
        }

        return false;
    }
}

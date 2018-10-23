<?php
/**
 * Contains code for the parcel point controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Front;

use Boxtal\BoxtalConnectPrestashop\Util\CartStorageUtil;
use Boxtal\BoxtalConnectPrestashop\Util\OrderStorageUtil;
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
        $boxtalConnect = \boxtalconnect::getInstance();
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
            $controller->registerStylesheet(
                'bx-parcel-point',
                'modules/'.$boxtalConnect->name.'/views/css/parcel-point.css',
                array('priority' => 100, 'server' => 'local')
            );
        } else {
            $controller->addJs(_MODULE_DIR_.'/'.$boxtalConnect->name.'/views/js/mapbox-gl.min.js');
            $controller->addCss(_MODULE_DIR_.'/'.$boxtalConnect->name.'/views/css/mapbox-gl.css', 'all');
            $controller->addJs(_MODULE_DIR_.'/'.$boxtalConnect->name.'/views/js/parcel-point.min.js');
            $controller->addCss(_MODULE_DIR_.'/'.$boxtalConnect->name.'/views/css/parcel-point.css', 'all');
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

        if (!isset($params['cart'])) {
            return null;
        }
        $cart = $params['cart'];
        CartStorageUtil::set($cart->id, 'bxParcelPoints', null);
        //phpcs:ignore
        $address = new \Address((int) $cart->id_address_delivery);

        $parcelPointNetworks = ShippingMethodUtil::getAllSelectedParcelPointNetworks();
        if (!empty($parcelPointNetworks)) {
            $lib      = new ApiClient(AuthUtil::getAccessKey(), AuthUtil::getSecretKey());
            $response = $lib->getParcelPoints(AddressUtil::convert($address), $parcelPointNetworks);
            if (! $response->isError() && property_exists($response->response, 'nearbyParcelPoints') && is_array($response->response->nearbyParcelPoints) && count($response->response->nearbyParcelPoints) > 0) {
                CartStorageUtil::set((int) $cart->id, 'bxParcelPoints', serialize($response->response));
                $boxtalConnect = \boxtalconnect::getInstance();
                $smarty = $boxtalConnect->getSmarty();
                $smarty->assign('bxCartId', (int) $cart->id);
                $host = \Tools::getShopProtocol().\Tools::getHttpHost().__PS_BASE_URI__;
                $smarty->assign('bxImgDir', $host.'modules/'.$boxtalConnect->name.'/views/img/');

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
     * Get closest parcel point.
     *
     * @param int    $cartId cart id.
     * @param string $id     shipping method id.
     *
     * @return mixed
     */
    public static function getClosestPoint($cartId, $id)
    {
        $parcelPoints = @unserialize(CartStorageUtil::get($cartId, 'bxParcelPoints'));
        $networks = ShippingMethodUtil::getSelectedParcelPointNetworks($id);
        if (property_exists($parcelPoints, 'nearbyParcelPoints') && is_array($parcelPoints->nearbyParcelPoints) && count($parcelPoints->nearbyParcelPoints) > 0) {
            foreach ($parcelPoints->nearbyParcelPoints as $parcelPoint) {
                if (property_exists($parcelPoint, 'parcelPoint') && property_exists($parcelPoint->parcelPoint, 'network') && in_array($parcelPoint->parcelPoint->network, $networks)) {
                    return $parcelPoint;
                }
            }
        }

        return null;
    }

    /**
     * Get chosen parcel point.
     *
     * @param int    $cartId cart id.
     * @param string $id     shipping method id.
     *
     * @return mixed
     */
    public static function getChosenPoint($cartId, $id)
    {
        $point = @unserialize(CartStorageUtil::get($cartId, 'bxChosenParcelPoint'.$id));
        if (false !== $point) {
            return $point;
        }

        return null;
    }

    /**
     * Order creation.
     *
     * @param $params array List of order params.
     *
     * @void
     */
    public function orderCreated($params)
    {

        if (!isset($params['cart'], $params['order'])) {
            return;
        }

        $cart = $params['cart'];
        $order = $params['order'];
        //phpcs:ignore
        $carrierId = $cart->id_carrier;

        $closestPoint = ParcelPointController::getClosestPoint($cart->id, $carrierId);
        if (null !== $closestPoint) {
            $point = ParcelPointController::getChosenPoint($cart->id, $carrierId);
            if (null === $point) {
                $point = $closestPoint;
            }

            CartStorageUtil::delete($cart->id);

            OrderStorageUtil::set($order->id, 'bxParcelPointCode', $point->parcelPoint->code);
            OrderStorageUtil::set($order->id, 'bxParcelPointNetwork', $point->parcelPoint->network);
        }

        return;
    }
}

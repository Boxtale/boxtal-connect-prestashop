<?php
/**
 * 2007-2019 PrestaShop
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
 * @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for the parcel point controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Front;

use Boxtal\BoxtalConnectPrestashop\Util\CartStorageUtil;
use Boxtal\BoxtalConnectPrestashop\Util\OrderStorageUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalConnectPrestashop\Util\AddressUtil;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ParcelPointUtil;
use BoxtalConnect;

/**
 * Parcel point controller class.
 *
 * @class       ParcelPointController
 */
class ParcelPointController
{
    /**
     * Add scripts.
     *
     * @return string html
     */
    public static function addScripts()
    {
        $boxtalConnect = BoxtalConnect::getInstance();
        $translation = array(
            'error' => array(
                'carrierNotFound' => $boxtalConnect->l('Unable to find carrier'),
                'couldNotSelectPoint' => $boxtalConnect->l('An error occurred during parcel point selection'),
            ),
            'text' => array(
                'openingHours' => $boxtalConnect->l('Opening hours'),
                'chooseParcelPoint' => $boxtalConnect->l('Choose this parcel point'),
                'closeMap' => $boxtalConnect->l('Close map'),
                'closedLabel' => $boxtalConnect->l('Closed     '),
            ),
            'day' => array(
                'MONDAY' => $boxtalConnect->l('monday'),
                'TUESDAY' => $boxtalConnect->l('tuesday'),
                'WEDNESDAY' => $boxtalConnect->l('wednesday'),
                'THURSDAY' => $boxtalConnect->l('thursday'),
                'FRIDAY' => $boxtalConnect->l('friday'),
                'SATURDAY' => $boxtalConnect->l('saturday'),
                'SUNDAY' => $boxtalConnect->l('sunday'),
            ),
        );

        $smarty = $boxtalConnect->getSmarty();
        $smarty->assign('translation', \Tools::jsonEncode($translation));
        $smarty->assign('token', \Tools::getToken(false));
        $smarty->assign('mapUrl', self::getMapUrl());
        $smarty->assign('mapLogoImageUrl', ConfigurationUtil::getMapLogoImageUrl());
        $smarty->assign('mapLogoHrefUrl', ConfigurationUtil::getMapLogoHrefUrl());

        $controller = $boxtalConnect->getCurrentController();
        if (method_exists($controller, 'registerJavascript')) {
            $controller->registerJavascript(
                'bx-promise-polyfill',
                'modules/' . $boxtalConnect->name . '/views/js/promise-polyfill.min.js',
                array('priority' => 98, 'server' => 'local')
            );
            $controller->registerJavascript(
                'bx-mapbox-gl',
                'modules/' . $boxtalConnect->name . '/views/js/mapbox-gl.min.js',
                array('priority' => 99, 'server' => 'local')
            );
            $controller->registerJavascript(
                'bx-parcel-point',
                'modules/' . $boxtalConnect->name . '/views/js/parcel-point.min.js',
                array('priority' => 100, 'server' => 'local')
            );
        } else {
            $controller->addJs('modules/' . $boxtalConnect->name . '/views/js/promise-polyfill.min.js');
            $controller->addJs('modules/' . $boxtalConnect->name . '/views/js/mapbox-gl.min.js');
            $controller->addJs('modules/' . $boxtalConnect->name . '/views/js/parcel-point.min.js');
        }
        if (method_exists($controller, 'registerStylesheet')) {
            $controller->registerStylesheet(
                'bx-mapbox-gl',
                'modules/' . $boxtalConnect->name . '/views/css/mapbox-gl.css',
                array('priority' => 100, 'server' => 'local')
            );
            $controller->registerStylesheet(
                'bx-parcel-point',
                'modules/' . $boxtalConnect->name . '/views/css/parcel-point.css',
                array('priority' => 100, 'server' => 'local')
            );
        } else {
            $controller->addCss('modules/' . $boxtalConnect->name . '/views/css/mapbox-gl.css', 'all');
            $controller->addCss('modules/' . $boxtalConnect->name . '/views/css/parcel-point.css', 'all');
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

        //phpcs:ignore
        $address = new \Address((int) $cart->id_address_delivery);
        $parcelPointNetworks = ShippingMethodUtil::getAllSelectedParcelPointNetworks();
        if (!empty($parcelPointNetworks)) {
            $lib = new ApiClient(
                AuthUtil::getAccessKey(ShopUtil::$shopGroupId, ShopUtil::$shopId),
                AuthUtil::getSecretKey(ShopUtil::$shopGroupId, ShopUtil::$shopId)
            );
            $response = $lib->getParcelPoints(AddressUtil::convert($address), $parcelPointNetworks);
            if (!$response->isError() && property_exists($response->response, 'nearbyParcelPoints')
                && is_array($response->response->nearbyParcelPoints)
                && count($response->response->nearbyParcelPoints) > 0) {
                CartStorageUtil::set((int) $cart->id, 'bxParcelPoints', serialize($response->response));
                $boxtalConnect = BoxtalConnect::getInstance();
                $smarty = $boxtalConnect->getSmarty();
                $smarty->assign('bxCartId', (int) $cart->id);

                return $boxtalConnect->displayTemplate('front/shipping-method/parcelPoint.tpl');
            }
        }
        CartStorageUtil::set($cart->id, 'bxParcelPoints', null);

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
     * @param int $cartId cart id
     * @param string $id shipping method id
     *
     * @return mixed
     */
    public static function getClosestPoint($cartId, $id)
    {
        $parcelPoints = @unserialize(CartStorageUtil::get($cartId, 'bxParcelPoints'));
        if (false === $parcelPoints) {
            return null;
        }
        $networks = ShippingMethodUtil::getSelectedParcelPointNetworks($id);
        if (property_exists($parcelPoints, 'nearbyParcelPoints') && is_array($parcelPoints->nearbyParcelPoints)
            && count($parcelPoints->nearbyParcelPoints) > 0) {
            foreach ($parcelPoints->nearbyParcelPoints as $parcelPoint) {
                if (property_exists($parcelPoint, 'parcelPoint')
                    && property_exists($parcelPoint->parcelPoint, 'network')
                    && in_array($parcelPoint->parcelPoint->network, $networks)) {
                    return ParcelPointUtil::normalizePoint($parcelPoint->parcelPoint);
                }
            }
        }

        return null;
    }

    /**
     * Order creation.
     *
     *  @param array $params list of order params
     *
     * @void
     */
    public static function orderCreated($params)
    {
        if (!isset($params['cart'], $params['order'])) {
            return;
        }

        $cart = $params['cart'];
        $order = $params['order'];
        //phpcs:ignore
        $carrierId = $cart->id_carrier;

        $orderPoint = null;
        $chosenPoint = ParcelPointUtil::getChosenPoint($cart->id, $carrierId);
        $closestPoint = ParcelPointController::getClosestPoint($cart->id, $carrierId);

        if (null !== $chosenPoint) {
            $orderPoint = $chosenPoint;
        } elseif (null !== $closestPoint) {
            $orderPoint = $closestPoint;
        }

        CartStorageUtil::delete($cart->id);
        ParcelPointUtil::setOrderParcelPoint($order->id, $orderPoint);
    }
}

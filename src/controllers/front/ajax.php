<?php
/**
 * Contains code for the front ajax controller class.
 */

use Boxtal\BoxtalConnectPrestashop\Controllers\Front\ParcelPointController;
use Boxtal\BoxtalConnectPrestashop\Util\CartStorageUtil;
use Boxtal\BoxtalConnectPrestashop\Util\CookieUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Util\ApiUtil;

/**
 * Front ajax controller class.
 *
 * @class       boxtalconnectajaxModuleFrontController
 *
 */
class BoxtalconnectAjaxModuleFrontController extends \ModuleFrontController
{

    /**
     * Ajax front controller.
     *
     * @void
     */
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        $route = Tools::getValue('route'); // Get route
        if ('getSelectedCarrierText' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$POST:
                        $selectedCarrierId = Tools::getValue('carrier');
                        $cartId = Tools::getValue('cartId');
                        $this->getSelectedCarrierTextHandler($cartId, $selectedCarrierId);
                        break;

                    default:
                        break;
                }
            }
        }

        if ('getPoints' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$POST:
                        $selectedCarrierId = Tools::getValue('carrier');
                        $cartId = Tools::getValue('cartId');
                        $this->getPointsHandler($cartId, $selectedCarrierId);
                        break;

                    default:
                        break;
                }
            }
        }

        if ('setPoint' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$POST:
                        $selectedCarrierId = Tools::getValue('carrier');
                        $cartId = Tools::getValue('cartId');
                        $name = Tools::getValue('name');
                        $code = Tools::getValue('code');
                        $network = Tools::getValue('network');
                        $this->setPointHandler($cartId, $selectedCarrierId, $name, $code, $network);
                        break;

                    default:
                        break;
                }
            }
        }

        ApiUtil::sendAjaxResponse(400);
    }

    /**
     * Returns selected carrier text.
     *
     * @param int    $cartId            cart id
     * @param string $selectedCarrierId selected carrier id
     *
     * @void
     */
    public function getSelectedCarrierTextHandler($cartId, $selectedCarrierId)
    {
        $text = "";
        $selectedCarrierCleanId = ShippingMethodUtil::getCleanId($selectedCarrierId);
        $boxtalconnect = \boxtalconnect::getInstance();
        $pointsResponse = @unserialize(CartStorageUtil::get((int) $cartId, 'bxParcelPoints'));
        if (false !== $pointsResponse) {
            $chosenParcelPoint = ParcelPointController::getChosenPoint((int) $cartId, $selectedCarrierCleanId);
            if (null === $chosenParcelPoint) {
                $closestParcelPoint = ParcelPointController::getClosestPoint((int) $cartId, $selectedCarrierCleanId);
                $text .= '<br/><span class="bx-parcel-client">'.$boxtalconnect->l('Closest parcel point:').' <span class="bw-parcel-name">'.$closestParcelPoint->parcelPoint->name.'</span></span>';
            } else {
                $text .= '<br/><span class="bx-parcel-client">'.$boxtalconnect->l('Your parcel point:').' <span class="bw-parcel-name">'.$chosenParcelPoint->parcelPoint->name.'</span></span>';
            }
            $text .= '<br/><span class="bx-select-parcel">'.$boxtalconnect->l('Choose another').'</span>';
        }

        ApiUtil::sendAjaxResponse(200, $text);
    }

    /**
     * Returns selected carrier text.
     *
     * @param int    $cartId            cart id
     * @param string $selectedCarrierId selected carrier id
     *
     * @void
     */
    public function getPointsHandler($cartId, $selectedCarrierId)
    {
        $selectedCarrierCleanId = ShippingMethodUtil::getCleanId($selectedCarrierId);
        $pointsResponse = @unserialize(CartStorageUtil::get((int) $cartId, 'bxParcelPoints'));
        $networks = ShippingMethodUtil::getSelectedParcelPointNetworks($selectedCarrierCleanId);
        if (false !== $pointsResponse && property_exists($pointsResponse, 'nearbyParcelPoints') && is_array($pointsResponse->nearbyParcelPoints) && count($pointsResponse->nearbyParcelPoints) > 0) {
            $points = array();
            foreach ($pointsResponse->nearbyParcelPoints as $parcelPoint) {
                if (property_exists($parcelPoint, 'parcelPoint') && property_exists($parcelPoint->parcelPoint, 'network') && in_array($parcelPoint->parcelPoint->network, $networks)) {
                    $points[] = $parcelPoint;
                }
            }
            if (!empty($points)) {
                $response = new \stdClass();
                $response->searchLocation = $pointsResponse->searchLocation;
                $response->nearbyParcelPoints = $points;
                ApiUtil::sendAjaxResponse(200, $response);
            }
        }

        ApiUtil::sendAjaxResponse(404);
    }

    /**
     * Returns selected carrier text.
     *
     * @param int    $cartId            cart id
     * @param string $selectedCarrierId selected carrier id
     * @param string $name              point name
     * @param string $code              point code
     * @param string $network           point network
     *
     * @void
     */
    public function setPointHandler($cartId, $selectedCarrierId, $name, $code, $network)
    {
        $selectedCarrierCleanId = ShippingMethodUtil::getCleanId($selectedCarrierId);

        if (null === $selectedCarrierCleanId || null === $cartId || null === $name || null === $code || null === $network) {
            ApiUtil::sendAjaxResponse(400);
        }

        $parcelPoint = new \stdClass();
        $parcelPoint->parcelPoint = new \stdClass();
        $parcelPoint->parcelPoint->network = $network;
        $parcelPoint->parcelPoint->code = $code;
        $parcelPoint->parcelPoint->name = $name;

        CartStorageUtil::set((int) $cartId, 'bxChosenParcelPoint'.$selectedCarrierCleanId, serialize($parcelPoint));

        ApiUtil::sendAjaxResponse(200);
    }
}

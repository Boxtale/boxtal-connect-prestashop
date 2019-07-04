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
 * Contains code for the front ajax controller class.
 */
use Boxtal\BoxtalConnectPrestashop\Controllers\Front\ParcelPointController;
use Boxtal\BoxtalConnectPrestashop\Util\ParcelPointUtil;
use Boxtal\BoxtalConnectPrestashop\Util\CartStorageUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Util\ApiUtil;

/**
 * Front ajax controller class.
 *
 * @class       boxtalconnectajaxModuleFrontController
 */
class BoxtalconnectAjaxModuleFrontController extends \ModuleFrontController
{

    private function getPostedParcelPoint()
    {
        return ParcelPointUtil::createParcelPoint(
            Tools::getValue('network'),
            Tools::getValue('code'),
            Tools::getValue('name'),
            Tools::getValue('address'),
            Tools::getValue('zipcode'),
            Tools::getValue('city'),
            Tools::getValue('country'),
            @json_decode(Tools::getValue('openingHours'))
        );
    }

    /**
     * Ajax front controller.
     *
     * @void
     */
    public function initContent()
    {
        if (!$this->isTokenValid()) {
            ApiUtil::sendAjaxResponse(403);
        }

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
                        $this->setPointHandler($cartId, $selectedCarrierId, $this->getPostedParcelPoint());
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
     * @param int $cartId cart id
     * @param string $selectedCarrierId selected carrier id
     *
     * @void
     */
    public function getSelectedCarrierTextHandler($cartId, $selectedCarrierId)
    {
        $text = '';
        $selectedCarrierCleanId = ShippingMethodUtil::getCleanId($selectedCarrierId);
        if (ShippingMethodUtil::hasSelectedParcelPointNetworks($selectedCarrierId)) {
            $pointsResponse = @unserialize(CartStorageUtil::get($cartId, 'bxParcelPoints'));
            if (false !== $pointsResponse) {
                $chosenParcelPoint = ParcelPointUtil::getChosenPoint($cartId, $selectedCarrierCleanId);
                $boxtalConnect = BoxtalConnect::getInstance();
                if (null === $chosenParcelPoint) {
                    $closestParcelPoint = ParcelPointController::getClosestPoint($cartId, $selectedCarrierCleanId);
                    if (null === $closestParcelPoint) {
                        ApiUtil::sendAjaxResponse(404);
                    }
                    $text .= '<br/><span class="bx-parcel-client">' . $boxtalConnect->l('Closest parcel point:')
                        . ' <span class="bw-parcel-name">' . $closestParcelPoint->name . '</span></span>';
                } else {
                    $text .= '<br/><span class="bx-parcel-client">' . $boxtalConnect->l('Your parcel point:')
                        . ' <span class="bw-parcel-name">' . $chosenParcelPoint->name . '</span></span>';
                }
                $text .= '<br/><span class="bx-select-parcel">' . $boxtalConnect->l('Choose another') . '</span>';
            }
        }
        ApiUtil::sendAjaxResponse(200, array('text' => $text));
    }

    /**
     * Returns selected carrier text.
     *
     * @param int $cartId cart id
     * @param string $selectedCarrierId selected carrier id
     *
     * @void
     */
    public function getPointsHandler($cartId, $selectedCarrierId)
    {
        $selectedCarrierCleanId = ShippingMethodUtil::getCleanId($selectedCarrierId);
        $pointsResponse = @unserialize(CartStorageUtil::get((int) $cartId, 'bxParcelPoints'));
        $networks = ShippingMethodUtil::getSelectedParcelPointNetworks($selectedCarrierCleanId);
        if (false !== $pointsResponse && property_exists($pointsResponse, 'nearbyParcelPoints')
            && is_array($pointsResponse->nearbyParcelPoints) && count($pointsResponse->nearbyParcelPoints) > 0) {
            $points = array();
            foreach ($pointsResponse->nearbyParcelPoints as $parcelPoint) {
                if (property_exists($parcelPoint, 'parcelPoint')
                    && property_exists($parcelPoint->parcelPoint, 'network')
                    && in_array($parcelPoint->parcelPoint->network, $networks)) {
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
     * @param int $cartId cart id
     * @param string $selectedCarrierId selected carrier id
     * @param mixed $parcelPoint
     *
     * @void
     */
    public function setPointHandler($cartId, $selectedCarrierId, $parcelPoint)
    {
        $selectedCarrierCleanId = ShippingMethodUtil::getCleanId($selectedCarrierId);

        if (null === $selectedCarrierCleanId || null === $cartId || null === $parcelPoint) {
            ApiUtil::sendAjaxResponse(400);
        }
        ParcelPointUtil::setChosenPoint((int) $cartId, $selectedCarrierCleanId, $parcelPoint);

        ApiUtil::sendAjaxResponse(200);
    }
}

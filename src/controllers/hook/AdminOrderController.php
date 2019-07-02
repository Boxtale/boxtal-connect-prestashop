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
 * Contains code for the admin order controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Hook;

use Boxtal\BoxtalConnectPrestashop\Util\ParcelPointUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\OrderUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\TrackingController;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;

/**
 * Admin order controller class.
 *
 * Generate the content to display on AdminOrder hook
 *
 * @class AdminOrderController
 */
class AdminOrderController
{
    private static $templateFile = 'hook/hookAdminOrder.tpl';

    private static function setBackofficeOrderParcelPointData($smarty, $orderId)
    {
        $parcelpoint = ParcelPointUtil::getOrderParcelPoint($orderId);
        $network = ConfigurationUtil::getParcelPointNetworks();
        $carrierId = OrderUtil::getCarrierId($orderId);
        $carrierNetworks = ShippingMethodUtil::getSelectedParcelPointNetworks($carrierId);

        $showParcelPoint = $parcelpoint !== null;
        $parcelpointShippingMethods = array();
        if ($showParcelPoint && $network[$parcelpoint->network]) {
            $parcelpointShippingMethods = $network[$parcelpoint->network];
        }
        $carrierShippingMethods = array();
        foreach ($carrierNetworks as $carrierNetwork) {
            $carrierShippingMethods = array_merge($carrierShippingMethods, $network[$carrierNetwork]);
        }

        $smarty->assign('showParcelPoint', $showParcelPoint);
        $smarty->assign('parcelpointBadge', $showParcelPoint ? 1 : 0);

        if ($showParcelPoint) {
            $showParcelPointAddress = !empty($parcelpoint->name)
                && !empty($parcelpoint->address)
                && !empty($parcelpoint->city)
                && !empty($parcelpoint->zipcode)
                && !empty($parcelpoint->country);

            $smarty->assign('parcelpoint', $parcelpoint);
            $smarty->assign('showParcelPointAddress', $showParcelPointAddress);
            $smarty->assign('parcelpointValidForCarrier', in_array($parcelpoint->network, $carrierNetworks));
            $smarty->assign('parcelpointShippingMethods', implode(', ', $parcelpointShippingMethods));
            $smarty->assign('carrierHasNetworks', count($carrierNetworks) > 0);
            $smarty->assign('carrierShippingMethods', implode(', ', $carrierShippingMethods));
        }
    }

    private static function setBackofficeOrderTrackingData($smarty, $orderId)
    {
        $tracking = TrackingController::getOrderTracking($orderId);

        $showTracking = null !== $tracking
            && property_exists($tracking, 'shipmentsTracking')
            && !empty($tracking->shipmentsTracking);

        $smarty->assign('showTracking', $showTracking);
        $smarty->assign('trackingBadge', $showTracking ? count($tracking->shipmentsTracking) : 0);

        if ($showTracking) {
            $smarty->assign('tracking', $tracking);
            $smarty->assign('dateFormat', \BoxtalConnect::getInstance()->l('Y-m-d H:i:s'));
        }
    }

    /**
     * Generate the content to display on AdminOrder hook
     *
     * @param mixed $params
     *
     * @return string extra content to display
     */
    public static function trigger($params)
    {
        if (!AuthUtil::canUsePlugin()) {
            return null;
        }

        $smarty = \BoxtalConnect::getInstance()->getSmarty();
        $orderId = (int) $params['id_order'];
        static::setBackofficeOrderParcelPointData($smarty, $orderId);
        static::setBackofficeOrderTrackingData($smarty, $orderId);

        return \BoxtalConnect::getInstance()->displayTemplate(static::$templateFile);
    }
}

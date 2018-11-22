<?php
/**
 * 2007-2018 PrestaShop
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
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for the tracking controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Misc;

use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
use Boxtal\BoxtalPhp\ApiClient;

/**
 * Tracking controller class.
 *
 * @class       TrackingController
 */
class TrackingController
{
    /**
     * Get order tracking.
     *
     * @param int $orderId order id
     *
     * @return object tracking
     */
    public static function getOrderTracking($orderId)
    {
        $lib = new ApiClient(AuthUtil::getAccessKey(ShopUtil::$shopGroupId, ShopUtil::$shopId), AuthUtil::getSecretKey(ShopUtil::$shopGroupId, ShopUtil::$shopId));
        $response = $lib->getOrder($orderId);
        if ($response->isError()) {
            return null;
        }

        return $response->response;
    }
}

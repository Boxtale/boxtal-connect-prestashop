<?php
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
 *
 */
class TrackingController
{

    /**
     * Get order tracking.
     *
     * @param int $orderId order id.
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

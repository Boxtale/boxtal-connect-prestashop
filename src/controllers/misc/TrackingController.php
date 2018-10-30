<?php
/**
 * Contains code for the tracking controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Misc;

use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPhp\ApiClient;
use boxtalconnect;

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
        $boxtalconnect = boxtalconnect::getInstance();
        $lib = new ApiClient(AuthUtil::getAccessKey($boxtalconnect->shopGroupId, $boxtalconnect->shopId), AuthUtil::getSecretKey($boxtalconnect->shopGroupId, $boxtalconnect->shopId));
        $response = $lib->getOrder($orderId);
        if ($response->isError()) {
            return null;
        }

        return $response->response;
    }
}

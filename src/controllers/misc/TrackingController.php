<?php
/**
 * Contains code for the tracking controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Misc;

use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
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
        return json_decode('{
  "reference": "xxxxxxx",
  "shipmentsTracking": [
    {
      "reference": "yyyyyy",
      "parcelsTracking": [
        {
          "reference": "zzzzzz",
          "status": "A",
          "trackingUrl": "http://anyurl",
          "trackingEvents": [
            {
              "date": "1977-04-22T06:00:00Z",
              "message": "message",
              "status": "A"
            }
          ]
        }
      ]
    }
  ]
}');
        $lib = new ApiClient(AuthUtil::getAccessKey(), AuthUtil::getSecretKey());
        $response = $lib->getOrder($orderId);
        if ($response->isError()) {
            return null;
        }
        return $response->response;
    }
}

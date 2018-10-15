<?php
/**
 * Contains code for the front ajax controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Front;

use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Util\ApiUtil;

/**
 * Front ajax controller class.
 *
 * @class       boxtalconnectajaxModuleFrontController
 *
 */
class boxtalconnectajaxModuleFrontController extends \ModuleFrontController
{

    /**
     * Ajax front controller.
     *
     * @void
     */
    public function initContent()
    {
        $this->ajax = true;
        $route = Tools::getValue('route'); // Get route
        if ('getSelectedCarrierText' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$POST:
                        $selectedCarrierId = Tools::getValue('carrier');
                        $this->getSelectedCarrierTextHandler($selectedCarrierId);
                        break;

                    default:
                        break;
                }
            }
        }

        ApiUtil::sendApiResponse(400);
    }

    /**
     * Returns selected carrier text.
     *
     * @param string $selectedCarrierId selected carrier id
     *
     * @void
     */
    public function getSelectedCarrierTextHandler($selectedCarrierId)
    {
        $text = "your parcel point!";
        ApiUtil::sendApiResponse(200, $text);
    }
}

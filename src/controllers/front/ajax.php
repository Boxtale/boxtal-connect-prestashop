<?php
/**
 * Contains code for the front ajax controller class.
 */

namespace Boxtal\BoxtalPrestashop\Controllers\Front;

use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalPrestashop\Util\ApiUtil;


/**
 * Front ajax controller class.
 *
 * @class       boxtalajaxModuleFrontController
 *
 */
class boxtalajaxModuleFrontController extends \ModuleFrontController
{

    public function initContent()
    {
        $this->ajax = true;
        $route = Tools::getValue('route'); // Get route
        die(Tools::jsonEncode("test"));
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

    public function getSelectedCarrierTextHandler($selectedCarrierId) {
        $text = "your parcel point!";
        ApiUtil::sendApiResponse(200, $text);
    }
}

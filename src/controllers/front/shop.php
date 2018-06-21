<?php
/**
 * Contains code for the shop rest controller.
 */

use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalPrestashop\Util\ApiUtil;
use Boxtal\BoxtalPrestashop\Util\AuthUtil;

/**
 * Shop class.
 *
 * Opens API endpoint to pair.
 */
class BoxtalShopModuleFrontController extends ModuleFrontController
{

    /**
     * Processes request.
     *
     * @void
     */
    public function postProcess()
    {

        $entityBody = file_get_contents('php://input');

        AuthUtil::authenticate($entityBody);
        $body = AuthUtil::decryptBody($entityBody);

        $route = Tools::getValue('route'); // Get route
        $html = '';

        if ('shop' === $route) {
            $html .= $this->pairingHandler($body);
        } else {
            exit;
        }

        die($html);
    }

    /**
     * Endpoint callback.
     *
     * @param array $body request body.
     *
     * @void
     */
    public function pairingHandler($body)
    {

        if (null === $body) {
            ApiUtil::sendApiResponse(400);
        }

        $accessKey   = null;
        $secretKey   = null;
        $callbackUrl = null;
        if (is_object($body) && property_exists($body, 'accessKey') && property_exists($body, 'secretKey')) {
            //phpcs:ignore
            $accessKey = $body->accessKey;
            //phpcs:ignore
            $secretKey = $body->secretKey;

            if (property_exists($body, 'pairCallbackUrl')) {
                //phpcs:ignore
                $callbackUrl = $body->pairCallbackUrl;
            }
        }

        if (null !== $accessKey && null !== $secretKey) {
            if (! AuthUtil::isPluginPaired()) { // initial pairing.
                AuthUtil::pairPlugin($accessKey, $secretKey);
                NoticeController::removeNotice('setupWizard');
                NoticeController::addNotice('pairing', array( 'result' => 1 ));
                ApiUtil::sendApiResponse(200);
            } else { // pairing update.
                if (null !== $callbackUrl) {
                    AuthUtil::pairPlugin($accessKey, $secretKey);
                    NoticeController::removeNotice('pairing');
                    AuthUtil::startPairingUpdate($callbackUrl);
                    NoticeController::addNotice('pairingUpdate');
                    ApiUtil::sendApiResponse(200);
                } else {
                    ApiUtil::sendApiResponse(403);
                }
            }
        } else {
            NoticeController::addNotice('pairing', array( 'result' => 0 ));
            ApiUtil::sendApiResponse(400);
        }
    }
}

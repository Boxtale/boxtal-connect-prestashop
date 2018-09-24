<?php
/**
 * Contains code for the shop rest controller.
 */

use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalPrestashop\Util\ApiUtil;
use Boxtal\BoxtalPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;

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

        if ('pair' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$PATCH:
                        $this->pairingHandler($body);
                        break;

                    default:
                        break;
                }
            }
        } elseif ('configuration' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$DELETE:
                        $this->deleteHandler($body);
                        break;

                    case RestClient::$PATCH:
                        $this->updateHandler($body);
                        break;

                    default:
                        break;
                }
            }
        }
        ApiUtil::sendApiResponse(400);
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
                NoticeController::removeNotice(NoticeController::$setupWizard);
                NoticeController::addNotice(NoticeController::$pairing, array( 'result' => 1 ));
                ApiUtil::sendApiResponse(200);
            } else { // pairing update.
                if (null !== $callbackUrl) {
                    AuthUtil::pairPlugin($accessKey, $secretKey);
                    NoticeController::removeNotice(NoticeController::$pairing);
                    AuthUtil::startPairingUpdate($callbackUrl);
                    NoticeController::addNotice(NoticeController::$pairingUpdate);
                    ApiUtil::sendApiResponse(200);
                } else {
                    ApiUtil::sendApiResponse(403);
                }
            }
        } else {
            NoticeController::addNotice(NoticeController::$pairing, array( 'result' => 0 ));
            ApiUtil::sendApiResponse(400);
        }
    }

    /**
     * Endpoint callback.
     *
     * @param object $body request body.
     *
     * @void
     */
    public function deleteHandler($body)
    {
        if (null === $body) {
            ApiUtil::sendApiResponse(400);
        }

        ConfigurationUtil::deleteConfiguration();
        ApiUtil::sendApiResponse(200);
    }

    /**
     * Endpoint callback.
     *
     * @param object $body request body.
     *
     * @void
     */
    public function updateHandler($body)
    {
        if (null === $body) {
            ApiUtil::sendApiResponse(400);
        }

        if (ConfigurationUtil::parseConfiguration($body)) {
            ApiUtil::sendApiResponse(200);
        }

        ApiUtil::sendApiResponse(400);
    }
}

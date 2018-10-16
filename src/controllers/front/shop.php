<?php
/**
 * Contains code for the shop rest controller.
 */

use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\ApiUtil;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;

/**
 * Shop class.
 *
 * Opens API endpoint to pair.
 */
class boxtalconnectShopModuleFrontController extends ModuleFrontController
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

        if (null === $body) {
            ApiUtil::sendApiResponse(400);
        }

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
                        $this->deleteConfigurationHandler($body);
                        break;

                    case RestClient::$PATCH:
                        $this->updateConfigurationHandler($body);
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
    public function deleteConfigurationHandler($body)
    {
        if (null === $body) {
            ApiUtil::sendApiResponse(400);
        }

        if (! is_object($body) || ! property_exists($body, 'accessKey') || $body->accessKey !== AuthUtil::getAccessKey()) {
            ApiUtil::sendApiResponse(403);
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
    public function updateConfigurationHandler($body)
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

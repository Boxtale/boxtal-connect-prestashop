<?php
/**
 * Contains code for the configuration rest controller.
 */

use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalPrestashop\Util\ApiUtil;
use Boxtal\BoxtalPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;

/**
 * Configuration class.
 *
 * Opens API endpoints to edit configuration.
 */
class BoxtalConfigurationModuleFrontController extends ModuleFrontController
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

        if ('configuration' === $route) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                switch ($_SERVER['REQUEST_METHOD']) {
                    case RestClient::$DELETE:
                        $this->deleteHandler($body);
                        break;

                    case RestClient::$PUT:
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
     * @param object $body request body.
     *
     * @void
     */
    public function deleteHandler($body)
    {
        if (null === $body) {
            ApiUtil::sendApiResponse(400);
        }

        $this::deleteConfiguration();
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

    /**
     * Delete configuration.
     *
     * @void
     */
    private static function deleteConfiguration()
    {
        ConfigurationUtil::delete('BX_ACCESS_KEY');
        ConfigurationUtil::delete('BX_SECRET_KEY');
        ConfigurationUtil::delete('BX_MAP_URL');
        ConfigurationUtil::delete('BX_TOKEN_URL');
        ConfigurationUtil::delete('BX_SIGNUP_URL');
        ConfigurationUtil::delete('BX_PP_OPERATORS');
        ConfigurationUtil::delete('BX_TRACKING_EVENT');
        ConfigurationUtil::delete('BX_PAIRING_UPDATE');
        NoticeController::removeAllNotices();
    }
}

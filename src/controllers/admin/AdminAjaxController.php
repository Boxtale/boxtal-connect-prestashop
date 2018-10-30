<?php
/**
 * Contains code for the ajax admin controller.
 */


use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;

/**
 * Ajax admin controller class.
 */
class AdminAjaxController extends \ModuleAdminController
{

    /**
     * Processes request.
     *
     * @void
     */
    public function postProcess()
    {

        parent::postProcess();

        $action = Tools::getValue('action'); // Get action

        $json = false;

        switch ($action) {
            case 'hideNotice':
                $json = $this->hideNoticeCallback();
                break;

            case 'pairingUpdateValidate':
                $json = $this->pairingUpdateValidateCallback();
                break;

            default:
                break;
        }

        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($json));
    }

    /**
     * Hide notice callback.
     *
     * @return boolean
     */
    public function hideNoticeCallback()
    {

        if (!Tools::getValue('noticeKey')) {
            return false;
        }
        $noticeKey = Tools::getValue('noticeKey');
        $noticeShopGroupId = Tools::getValue('noticeShopGroupId');
        $noticeShopId = Tools::getValue('noticeShopId');
        NoticeController::removeNotice($noticeKey, $noticeShopGroupId, $noticeShopId);

        return true;
    }

    /**
     * Ajax callback. Validate pairing update.
     *
     * @void
     */
    public function pairingUpdateValidateCallback()
    {
        if (! isset($_REQUEST['approve'])) {
            wp_send_json_error('missing input');
        }
        $approve = sanitize_text_field(wp_unslash($_REQUEST['approve']));

        $boxtalconnect = boxtalconnect::getInstance();
        $lib = new ApiClient(
            AuthUtil::getAccessKey($boxtalconnect->shopGroupId, $boxtalconnect->shopId),
            AuthUtil::getSecretKey($boxtalconnect->shopGroupId, $boxtalconnect->shopId)
        );
        //phpcs:ignore
        $response = $lib->restClient->request(
            RestClient::$PATCH,
            ConfigurationUtil::get('BW_PAIRING_UPDATE', $boxtalconnect->shopGroupId, $boxtalconnect->shopId),
            array( 'approve' => $approve )
        );

        if (! $response->isError()) {
            AuthUtil::endPairingUpdate($boxtalconnect->shopGroupId, $boxtalconnect->shopId);
            NoticeController::removeNotice(NoticeController::$pairingUpdate, $boxtalconnect->shopGroupId, $boxtalconnect->shopId);
            NoticeController::addNotice(NoticeController::$pairing, $boxtalconnect->shopGroupId, $boxtalconnect->shopId, array( 'result' => 1 ));
            wp_send_json(true);
        } else {
            wp_send_json_error('pairing validation failed');
        }
    }
}

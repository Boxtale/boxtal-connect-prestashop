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
        NoticeController::removeNotice($noticeKey);

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

        $lib = new ApiClient(AuthUtil::getAccessKey(), AuthUtil::getSecretKey());
        //phpcs:ignore
        $response = $lib->restClient->request( RestClient::$PATCH, ConfigurationUtil::get( 'BW_PAIRING_UPDATE' ), array( 'approve' => $approve ) );

        if (! $response->isError()) {
            AuthUtil::endPairingUpdate();
            NoticeController::removeNotice(NoticeController::$pairingUpdate);
            NoticeController::addNotice(NoticeController::$pairing, array( 'result' => 1 ));
            wp_send_json(true);
        } else {
            wp_send_json_error('pairing validation failed');
        }
    }
}

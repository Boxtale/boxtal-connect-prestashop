<?php
/**
 * Contains code for the ajax admin controller.
 */


use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;

/**
 * Ajax admin controller class.
 */
class BoxtalAjaxModuleAdminController extends \ModuleAdminController
{

    /**
     * Processes request.
     *
     * @void
     */
    public function postProcess()
    {

        $endpoint = Tools::getValue('endpoint'); // Get endpoint

        $json = false;

        switch ($endpoint) {
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

        if (!Tools::getValue('noticeId')) {
            return false;
        }
        $noticeId = Tools::getValue('noticeId');
        $noticeController = new NoticeController();
        $noticeController->removeNotice($noticeId);

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

        $lib = new ApiClient(Auth_Util::get_access_key(), Auth_Util::get_secret_key());
        //phpcs:ignore
        $response = $lib->restClient->request( RestClient::$PATCH, get_option( 'BW_PAIRING_UPDATE' ), array( 'approve' => $approve ) );

        if (! $response->isError()) {
            Auth_Util::end_pairing_update();
            Notice_Controller::remove_notice('pairing-update');
            Notice_Controller::add_notice('pairing', array( 'result' => 1 ));
            wp_send_json(true);
        } else {
            wp_send_json_error('pairing validation failed');
        }
    }
}

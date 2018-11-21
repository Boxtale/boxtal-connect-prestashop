<?php
/**
 * Contains code for the ajax admin controller.
 */


use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\ApiUtil;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;

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

        header('Content-Type: application/json; charset=utf-8');

        switch ($action) {
            case 'hideNotice':
                $this->hideNoticeCallback();
                break;

            case 'pairingUpdateValidate':
                $this->pairingUpdateValidateCallback();
                break;

            default:
                break;
        }
    }

    /**
     * Hide notice callback.
     *
     * @void
     */
    public function hideNoticeCallback()
    {

        if (!Tools::getValue('noticeKey')) {
            ApiUtil::sendAjaxResponse(400);
        }
        $noticeKey = Tools::getValue('noticeKey');
        $noticeShopGroupId = "" === Tools::getValue('noticeShopGroupId') ? null : Tools::getValue('noticeShopGroupId');
        $noticeShopId = "" === Tools::getValue('noticeShopId') ? null : Tools::getValue('noticeShopId');
        NoticeController::removeNotice($noticeKey, $noticeShopGroupId, $noticeShopId);
        ApiUtil::sendAjaxResponse(200);
    }

    /**
     * Ajax callback. Validate pairing update.
     *
     * @void
     */
    public function pairingUpdateValidateCallback()
    {
        if (! Tools::isSubmit('approve')) {
            ApiUtil::sendAjaxResponse(400, 'missing input');
        }
        $approve = Tools::getValue('approve');

        $lib = new ApiClient(
            AuthUtil::getAccessKey(ShopUtil::$shopGroupId, ShopUtil::$shopId),
            AuthUtil::getSecretKey(ShopUtil::$shopGroupId, ShopUtil::$shopId)
        );
        //phpcs:ignore
        $response = $lib->restClient->request(
            RestClient::$PATCH,
            ConfigurationUtil::get('BW_PAIRING_UPDATE'),
            array( 'approve' => $approve )
        );

        if (! $response->isError()) {
            AuthUtil::endPairingUpdate();
            NoticeController::removeNotice(NoticeController::$pairingUpdate, ShopUtil::$shopGroupId, ShopUtil::$shopId);
            NoticeController::addNotice(NoticeController::$pairing, ShopUtil::$shopGroupId, ShopUtil::$shopId, array( 'result' => 1 ));
            ApiUtil::sendAjaxResponse(200);
        } else {
            ApiUtil::sendAjaxResponse(404);
        }
    }
}

<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Boxtal <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for the ajax admin controller.
 */
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\ApiUtil;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;

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
        $noticeShopGroupId = '' === Tools::getValue('noticeShopGroupId') ? null : Tools::getValue('noticeShopGroupId');
        $noticeShopId = '' === Tools::getValue('noticeShopId') ? null : Tools::getValue('noticeShopId');
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
        if (!Tools::isSubmit('approve')) {
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
            ConfigurationUtil::get('BX_PAIRING_UPDATE'),
            array('approve' => $approve)
        );

        if (!$response->isError()) {
            AuthUtil::endPairingUpdate();
            NoticeController::removeNotice(NoticeController::$pairingUpdate, ShopUtil::$shopGroupId, ShopUtil::$shopId);
            NoticeController::addNotice(
                NoticeController::$pairing,
                ShopUtil::$shopGroupId,
                ShopUtil::$shopId,
                array('result' => 1)
            );
            ApiUtil::sendAjaxResponse(200);
        } else {
            ApiUtil::sendAjaxResponse(404);
        }
    }
}

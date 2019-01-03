<?php
/**
 * 2007-2019 PrestaShop
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
 * @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for api util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

use BoxtalConnect;

/**
 * Api util class.
 *
 * Helper to manage API responses.
 */
class ApiUtil
{
    /**
     * Send API request response.
     *
     * @param int $code http code
     * @param mixed $body to send along response
     *
     * @void
     */
    public static function sendApiResponse($code, $body = null)
    {
        $boxtalConnect = BoxtalConnect::getInstance();
        header('X-Version: ' . $boxtalConnect->version);
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        if (null !== $body) {
            echo AuthUtil::encryptBody($body);
        }
        die();
    }

    /**
     * Send Ajax request response.
     *
     * @param int $code http code
     * @param mixed $body to send along response
     *
     * @void
     */
    public static function sendAjaxResponse($code, $body = null)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        if (null !== $body) {
            echo json_encode($body);
        }
        die();
    }
}

<?php
/**
 * Contains code for api util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

/**
 * Api util class.
 *
 * Helper to manage API responses.
 */
class ApiUtil
{

    /**
     * API request validation.
     *
     * @param integer $code http code.
     * @param mixed   $body to send along response.
     *
     * @void
     */
    public static function sendApiResponse($code, $body = null)
    {
        http_response_code($code);
        if (null !== $body) {
            echo AuthUtil::encryptBody($body);
        }
        die();
    }
}

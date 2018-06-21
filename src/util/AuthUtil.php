<?php
/**
 * Contains code for auth util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

/**
 * Auth util class.
 *
 * Helper to manage API auth.
 */
class AuthUtil
{

    /**
     * API request validation.
     *
     * @param string $body encrypted body.
     *
     * @return mixed
     */
    public static function authenticate($body)
    {
        $publicKey = file_get_contents(realpath(dirname(__DIR__)).DIRECTORY_SEPARATOR.'resource'.DIRECTORY_SEPARATOR.'publickey');
        $decrypted  = '';
        if (openssl_public_decrypt(base64_decode($body), $decrypted, $publicKey)) {
            return true;
        }

        return ApiUtil::sendApiResponse(401);
    }

    /**
     * Is plugin paired.
     *
     * @return boolean
     */
    public static function isPluginPaired()
    {
        return null !== self::getAccessKey() && null !== self::getSecretKey();
    }

    /**
     * Can use plugin.
     *
     * @return boolean
     */
    public static function canUsePlugin()
    {
        return false !== self::isPluginPaired() && false === ConfigurationUtil::get('BX_PAIRING_UPDATE');
    }

    /**
     * Pair plugin.
     *
     * @param string $accessKey API access key.
     * @param string $secretKey API secret key.
     *
     * @void
     */
    public static function pairPlugin($accessKey, $secretKey)
    {
        ConfigurationUtil::set('BX_ACCESS_KEY', $accessKey);
        ConfigurationUtil::set('BX_SECRET_KEY', $secretKey);
    }

    /**
     * Start pairing update (puts plugin on hold).
     *
     * @param string $callbackUrl callback url.
     *
     * @void
     */
    public static function startPairingUpdate($callbackUrl)
    {
        ConfigurationUtil::set('BX_PAIRING_UPDATE', $callbackUrl);
    }

    /**
     * End pairing update (release plugin).
     *
     * @void
     */
    public static function endPairingUpdate()
    {
        ConfigurationUtil::delete('BX_PAIRING_UPDATE');
    }

    /**
     * Request body decryption.
     *
     * @param string $body encrypted body.
     *
     * @return mixed
     */
    public static function decryptBody($body)
    {
        // phpcs:ignore
        $publicKey = file_get_contents(realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'publickey');
        $decrypted  = '';
        if (openssl_public_decrypt(base64_decode($body), $decrypted, $publicKey)) {
            return json_decode($decrypted);
        }

        return null;
    }

    /**
     * Request body decryption.
     *
     * @param mixed $body encrypted body.
     *
     * @return mixed
     */
    public static function encryptBody($body)
    {
        // phpcs:ignore
        $publicKey = file_get_contents(realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'publickey');
        $encrypted  = '';
        if (is_array($body)) {
            $body = json_encode($body);
        }
        if (openssl_public_encrypt($body, $encrypted, $publicKey)) {
            return base64_encode($encrypted);
        }

        return null;
    }

    /**
     * Get access key.
     *
     * @return string
     */
    public static function getAccessKey()
    {
        return ConfigurationUtil::get('BX_ACCESS_KEY');
    }

    /**
     * Get secret key.
     *
     * @return string
     */
    public static function getSecretKey()
    {
        return ConfigurationUtil::get('BW_SECRET_KEY');
    }
}

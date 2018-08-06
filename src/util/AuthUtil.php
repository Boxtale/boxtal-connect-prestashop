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

        ApiUtil::sendApiResponse(401);
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
        $key = self::getRandomKey();
        if (null === $key) {
            return null;
        }

        return json_encode(
            array(
                'encryptedKey'  => MiscUtil::base64OrNull(self::encryptPublicKey($key)),
                'encryptedData' => MiscUtil::base64OrNull(self::encryptRc4((is_array($body) ? json_encode($body) : $body), $key)),
            )
        );
    }

    /**
     * Get random encryption key.
     *
     * @return string
     */
    public static function getRandomKey()
    {
        //phpcs:ignore
        $randomKey = openssl_random_pseudo_bytes(200);
        if (false === $randomKey) {
            return null;
        }

        return bin2hex($randomKey);
    }

    /**
     * Encrypt with public key.
     *
     * @param string $str string to encrypt.
     *
     * @return array bytes array
     */
    public static function encryptPublicKey($str)
    {
        // phpcs:ignore
        $publicKey = file_get_contents(realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'publickey');
        $encrypted  = '';
        if (openssl_public_encrypt($str, $encrypted, $publicKey)) {
            return $encrypted;
        }

        return null;
    }

    /**
     * RC4 symmetric cipher encryption/decryption
     *
     * @param string $str string to be encrypted/decrypted.
     * @param array  $key secret key for encryption/decryption.
     *
     * @return array bytes array
     */
    public static function encryptRc4($str, $key)
    {
        $s = array();
        for ($i = 0; $i < 256; $i++) {
            $s[$i] = $i;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++) {
            $j       = ( $j + $s[$i] + ord($key[$i % strlen($key)]) ) % 256;
            $x       = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
        }
        $i      = 0;
        $j      = 0;
        $res    = '';
        $length = strlen($str);
        for ($y = 0; $y < $length; $y++) {
            //phpcs:ignore
            $i       = ( $i + 1 ) % 256;
            $j       = ( $j + $s[$i] ) % 256;
            $x       = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
            $res    .= $str[$y] ^ chr($s[( $s[$i] + $s[$j] ) % 256]);
        }

        return $res;
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
        return ConfigurationUtil::get('BX_SECRET_KEY');
    }
}

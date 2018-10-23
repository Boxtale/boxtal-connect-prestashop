<?php
/**
 * Contains code for cart storage util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

/**
 * Cart storage util class.
 *
 * Helper to manage cart extra storage.
 */
class CartStorageUtil
{

    /**
     * Get cart storage value.
     *
     * @param int    $cartId cart id.
     * @param string $key    name of variable.
     *
     * @return mixed value
     */
    public static function get($cartId, $key)
    {
        $sql = new \DbQuery();
        $sql->select('cs.value');
        $sql->from('bx_cart_storage', 'cs');
        $sql->where('cs.id_cart='.(int) $cartId);
        $sql->where('cs.key="'.pSQL($key).'"');

        $result = \Db::getInstance()->executeS($sql);

        if (isset($result[0]['value'])) {
            return $result[0]['value'];
        }

        return null;
    }

    /**
     * Set cart storage value.
     *
     * @param int          $cartId cart id.
     * @param string       $key    name of variable.
     * @param string|array $value  value of variable.
     *
     * @void
     */
    public static function set($cartId, $key, $value)
    {
        $data = array(
            'id_cart' => (int) $cartId,
            'key' => pSQL($key),
            'value' => pSQL($value),
        );
        \Db::getInstance()->insert(
            'bx_cart_storage',
            $data,
            true,
            true,
            \DB::REPLACE
        );
    }

    /**
     * Delete obsolete cart storage value.
     *
     * @param int $cartId cart id.
     *
     * @void
     */
    public static function delete($cartId)
    {
        \Db::getInstance()->delete(
            'bx_cart_storage',
            'id_cart = '.$cartId
        );
    }
}

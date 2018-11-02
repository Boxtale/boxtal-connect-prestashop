<?php
/**
 * Contains code for order storage util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

/**
 * Order storage util class.
 *
 * Helper to manage order extra storage.
 */
class OrderStorageUtil
{

    /**
     * Get order storage row.
     *
     * @param int    $orderId order id.
     * @param string $key     name of variable.
     *
     * @return mixed value
     */
    public static function getRow($orderId, $key)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('bx_order_storage', 'os');
        $sql->where('os.id_order='.(int) $orderId);
        $sql->where('os.key="'.pSQL($key).'"');
        $sql->where('os.id_shop_group='.ShopUtil::$shopGroupId);
        $sql->where('os.id_shop='.ShopUtil::$shopId);

        $result = \Db::getInstance()->executeS($sql);

        if (isset($result[0])) {
            return $result[0];
        }

        return null;
    }

    /**
     * Get order storage value.
     *
     * @param int    $orderId order id.
     * @param string $key     name of variable.
     *
     * @return mixed value
     */
    public static function get($orderId, $key)
    {
        $row = self::getRow($orderId, $key);

        return isset($row['value']) ? $row['value'] : null;
    }

    /**
     * Set order storage value.
     *
     * @param int          $orderId order id.
     * @param string       $key     name of variable.
     * @param string|array $value   value of variable.
     *
     * @void
     */
    public static function set($orderId, $key, $value)
    {
        $data = array(
            'id_order' => (int) $orderId,
            'id_shop_group' => ShopUtil::$shopGroupId,
            'id_shop' => ShopUtil::$shopId,
            'key' => pSQL($key),
            'value' => pSQL($value),
        );

        \Db::getInstance()->insert(
            'bx_order_storage',
            $data,
            true,
            true,
            \Db::REPLACE
        );
    }
}

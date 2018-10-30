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
     * Get order storage value.
     *
     * @param int    $orderId     order id.
     * @param string $key         name of variable.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     *
     * @return mixed value
     */
    public static function get($orderId, $key, $shopGroupId, $shopId)
    {
        $sql = new \DbQuery();
        $sql->select('os.value');
        $sql->from('bx_order_storage', 'os');
        $sql->where('os.id_order='.(int) $orderId);
        $sql->where('os.key="'.pSQL($key).'"');
        $sql->where('os.id_shop_group='.$shopGroupId);
        $sql->where('os.id_shop='.$shopId);

        $result = \Db::getInstance()->executeS($sql);

        if (isset($result[0]['value'])) {
            return $result[0]['value'];
        }

        return null;
    }

    /**
     * Set order storage value.
     *
     * @param int          $orderId     order id.
     * @param string       $key         name of variable.
     * @param string|array $value       value of variable.
     * @param int          $shopGroupId shop group id.
     * @param int          $shopId      shop id.
     *
     * @void
     */
    public static function set($orderId, $key, $value, $shopGroupId, $shopId)
    {
        $data = array(
            'id_order' => (int) $orderId,
            'id_shop_group' => $shopGroupId,
            'id_shop' => $shopId,
            'key' => pSQL($key),
            'value' => pSQL($value),
        );
        \Db::getInstance()->insert(
            'bx_order_storage',
            $data,
            true,
            true,
            \DB::REPLACE
        );
    }
}

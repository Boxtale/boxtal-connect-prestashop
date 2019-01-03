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
     * @param int $orderId order id
     * @param string $key name of variable
     *
     * @return mixed value
     */
    public static function getRow($orderId, $key)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('bx_order_storage', 'os');
        $sql->where('os.id_order=' . (int) $orderId);
        $sql->where('os.key="' . pSQL($key) . '"');
        $sql->where('os.id_shop_group=' . (int) ShopUtil::$shopGroupId);
        $sql->where('os.id_shop=' . (int) ShopUtil::$shopId);

        $result = \Db::getInstance()->executeS($sql);

        if (isset($result[0])) {
            return $result[0];
        }

        return null;
    }

    /**
     * Get order storage value.
     *
     * @param int $orderId order id
     * @param string $key name of variable
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
     * @param int $orderId order id
     * @param string $key name of variable
     * @param string|array $value value of variable
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

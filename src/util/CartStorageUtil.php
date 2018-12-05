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
     * @param int $cartId cart id
     * @param string $key name of variable
     *
     * @return mixed value
     */
    public static function get($cartId, $key)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('bx_cart_storage', 'cs');
        $sql->where('cs.id_cart=' . (int) $cartId);
        $sql->where('cs.key="' . pSQL($key) . '"');
        if (null === ShopUtil::$shopGroupId) {
            $sql->where('cs.id_shop_group IS NULL');
        } else {
            $sql->where('cs.id_shop_group=' . (int) ShopUtil::$shopGroupId);
        }

        if (null === ShopUtil::$shopId) {
            $sql->where('cs.id_shop IS NULL');
        } else {
            $sql->where('cs.id_shop=' . (int) ShopUtil::$shopId);
        }

        $result = \Db::getInstance()->executeS($sql);

        if (isset($result[0]['value'])) {
            return $result[0]['value'];
        }

        return null;
    }

    /**
     * Set cart storage value.
     *
     * @param int $cartId cart id
     * @param string $key name of variable
     * @param string|array $value value of variable
     *
     * @void
     */
    public static function set($cartId, $key, $value)
    {
        $data = array(
            'id_cart' => (int) $cartId,
            'id_shop_group' => ShopUtil::$shopGroupId,
            'id_shop' => ShopUtil::$shopId,
            'key' => pSQL($key),
            'value' => pSQL($value),
        );

        \Db::getInstance()->insert(
            'bx_cart_storage',
            $data,
            true,
            true,
            \Db::REPLACE
        );
    }

    /**
     * Delete obsolete cart storage value.
     *
     * @param int $cartId cart id
     *
     * @void
     */
    public static function delete($cartId)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'bx_cart_storage` WHERE id_cart="' . $cartId . '" ';
        if (null === ShopUtil::$shopGroupId) {
            $sql .= 'AND id_shop_group IS NULL ';
        } else {
            $sql .= 'AND id_shop_group=' . ShopUtil::$shopGroupId . ' ';
        }

        if (null === ShopUtil::$shopId) {
            $sql .= 'AND id_shop IS NULL ';
        } else {
            $sql .= 'AND id_shop=' . ShopUtil::$shopId . ' ';
        }

        \Db::getInstance()->execute($sql);
    }
}

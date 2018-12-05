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
 * Contains code for order util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

/**
 * Order util class.
 *
 * Helper to manage orders.
 */
class OrderUtil
{
    /**
     * Get order data.
     *
     * @return array|false|null
     */
    public static function getOrders()
    {
        $sql = new \DbQuery();
        $sql->select(
            'o.id_order, o.reference, c.firstname, c.lastname, c.company, a.address1, a.address2, a.city, ' .
            'a.postcode, co.iso_code as country_iso, s.iso_code as state_iso, c.email, a.phone, a.phone_mobile, ' .
            'osl.name as status, ca.name as shippingMethod, o.total_shipping_tax_excl as shippingAmount, ' .
            'o.date_add as creationDate, o.total_paid_tax_excl as orderAmount'
        );
        $sql->from('orders', 'o');
        $sql->innerJoin('customer', 'c', 'o.id_customer = c.id_customer');
        $sql->innerJoin('address', 'a', 'o.id_address_delivery = a.id_address');
        $sql->innerJoin('country', 'co', 'a.id_country = co.id_country');
        $sql->leftJoin('state', 's', 'a.id_state = s.id_state');
        $sql->innerJoin('order_state', 'os', 'o.current_state = os.id_order_state');
        $sql->innerJoin('order_state_lang', 'osl', 'os.id_order_state = osl.id_order_state');
        $sql->innerJoin('order_carrier', 'oc', 'o.id_order = oc.id_order');
        $sql->innerJoin('carrier', 'ca', 'oc.id_carrier = ca.id_carrier');
        $sql->where(
            'os.shipped=0 AND o.id_shop_group=' . (int) ShopUtil::$shopGroupId . ' AND o.id_shop=' . (int) ShopUtil::$shopId
        );
        $sql->groupBy('o.reference');
        $sql->orderBy('creationDate desc');

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Get order data.
     *
     * @param int $orderId order id
     *
     * @return array|false|null
     */
    public static function getItemsFromOrder($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('od.product_id, od.product_weight, od.product_price, od.product_quantity, od.product_name');
        $sql->from('order_detail', 'od');
        $sql->where('od.id_order = ' . (int) $orderId);

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Get order status multilingual.
     *
     * @param int $orderId order id
     *
     * @return array
     */
    public static function getStatusMultilingual($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('l.language_code, os.name');
        $sql->from('orders', 'o');
        $sql->innerJoin('order_state_lang', 'os', 'o.current_state = os.id_order_state AND o.id_order = ' . (int) $orderId);
        $sql->innerJoin('lang', 'l', 'os.id_lang = l.id_lang');
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return array();
        }

        $translations = array();
        foreach ($result as $statusTranslation) {
            $translations[\Tools::strtolower(str_replace('-', '_', $statusTranslation['language_code']))]
                = $statusTranslation['name'];
        }

        return $translations;
    }

    /**
     * Get order status id.
     *
     * @param int $orderId order id
     *
     * @return int
     */
    public static function getStatusId($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('o.current_state');
        $sql->from('orders', 'o');
        $sql->where('o.id_order = ' . (int) $orderId);
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return null;
        }

        $row = array_shift($result);

        return (int) $row['current_state'];
    }

    /**
     * Get order reference.
     *
     * @param int $orderId order id
     *
     * @return int
     */
    public static function getOrderReference($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('o.reference');
        $sql->from('orders', 'o');
        $sql->where('o.id_order = ' . (int) $orderId);
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return null;
        }

        $row = array_shift($result);

        return $row['reference'];
    }

    /**
     * Get carrier reference.
     *
     * @param int $orderId order id
     *
     * @return int
     */
    public static function getCarrierReference($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('c.id_reference');
        $sql->from('orders', 'o');
        $sql->innerJoin('carrier', 'c', 'c.id_carrier = o.id_carrier');
        $sql->where('o.id_order = ' . (int) $orderId);
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return null;
        }

        $row = array_shift($result);

        return (int) $row['id_reference'];
    }

    /**
     * Get carrier id.
     *
     * @param int $orderId order id
     *
     * @return int
     */
    public static function getCarrierId($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('oc.id_carrier');
        $sql->from('orders', 'o');
        $sql->innerJoin('order_carrier', 'oc', 'o.id_order = oc.id_order');
        $sql->where('o.id_order = ' . (int) $orderId);
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return null;
        }

        $row = array_shift($result);

        return (int) $row['id_carrier'];
    }

    /**
     * Get order statuses.
     *
     * @param string $langId language id
     *
     * @return array|false|null
     */
    public static function getOrderStatuses($langId)
    {
        $sql = new \DbQuery();
        $sql->select('os.id_order_state, osl.name');
        $sql->from('order_state', 'os');
        $sql->innerJoin('order_state_lang', 'osl', 'os.id_order_state = osl.id_order_state');
        $sql->where('osl.id_lang = ' . (int) $langId);
        $sql->where('os.deleted = 0');

        return \Db::getInstance()->executeS($sql);
    }
}

<?php
/**
 * Contains code for order util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

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
     * @return
     */
    public static function getOrders()
    {
        $sql = new \DbQuery();
        $sql->select('o.id_order, c.firstname, c.lastname, c.company, a.address1, a.address2, a.city, a.postcode, co.iso_code as coutry_iso, s.iso_code as state_iso, c.email, a.phone');
        $sql->from('orders', 'o');
        $sql->innerJoin('customer', 'c', 'o.id_customer = c.id_customer');
        $sql->innerJoin('address', 'a', 'o.id_address_delivery = a.id_address');
        $sql->innerJoin('country', 'co', 'a.id_country = co.id_country');
        $sql->innerJoin('state', 's', 'a.id_state = s.id_state');
        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Get order data.
     *
     * @param $orderId int order id
     * @return
     */
    public static function getItemsFromOrder($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('od.product_weight, od.product_price, od.product_quantity, od.product_name');
        $sql->from('order_detail', 'od');
        $sql->where('od.id_order = ' . $orderId);
        return \Db::getInstance()->executeS($sql);
    }
}

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
     * @return array|false|null
     */
    public static function getOrders()
    {
        $sql = new \DbQuery();
        $sql->select('o.id_order, o.reference, c.firstname, c.lastname, c.company, a.address1, a.address2, a.city, a.postcode, co.iso_code as country_iso, s.iso_code as state_iso, c.email, a.phone, osl.name as status, ca.name as shippingMethod, o.total_shipping_tax_excl as shippingAmount, o.date_add as creationDate, o.total_paid_tax_excl as orderAmount');
        $sql->from('orders', 'o');
        $sql->innerJoin('customer', 'c', 'o.id_customer = c.id_customer');
        $sql->innerJoin('address', 'a', 'o.id_address_delivery = a.id_address');
        $sql->innerJoin('country', 'co', 'a.id_country = co.id_country');
        $sql->innerJoin('state', 's', 'a.id_state = s.id_state');
        $sql->innerJoin('order_state', 'os', 'o.current_state = os.id_order_state');
        $sql->innerJoin('order_state_lang', 'osl', 'os.id_order_state = osl.id_order_state');
        $sql->innerJoin('order_carrier', 'oc', 'o.id_order = oc.id_order');
        $sql->innerJoin('carrier', 'ca', 'oc.id_carrier = ca.id_carrier');
        $sql->where('os.shipped=0');
        $sql->groupBy('o.reference');

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
        $sql->select('od.product_weight, od.product_price, od.product_quantity, od.product_name');
        $sql->from('order_detail', 'od');
        $sql->where('od.id_order = '.$orderId);

        return \Db::getInstance()->executeS($sql);
    }
}

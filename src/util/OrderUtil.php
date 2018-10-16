<?php
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
        $sql->select('od.product_id, od.product_weight, od.product_price, od.product_quantity, od.product_name');
        $sql->from('order_detail', 'od');
        $sql->where('od.id_order = '.(int) $orderId);

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
        $sql->select('l.locale, os.name');
        $sql->from('orders', 'o');
        $sql->innerJoin('order_state_lang', 'os', 'o.current_state = os.id_order_state AND o.id_order = '.$orderId);
        $sql->innerJoin('lang', 'l', 'os.id_lang = l.id_lang');
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return array();
        }

        $translations = array();
        foreach ($result as $statusTranslation) {
            $translations[str_replace('-', '_', $statusTranslation['locale'])] = $statusTranslation['name'];
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
        $sql->where('o.id_order = '.$orderId);
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return null;
        }

        $row = array_shift($result);

        return (int) $row['current_state'];
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
        $sql->select('o.id_carrier');
        $sql->from('orders', 'o');
        $sql->where('o.id_order = '.$orderId);
        $result = \Db::getInstance()->executeS($sql);

        if (!is_array($result)) {
            return null;
        }

        $row = array_shift($result);

        return (int) $row['id_carrier'];
    }
}

<?php
/**
 * Test order helper
 */

use Boxtal\BoxtalConnectPrestashop\Util\AddressUtil;

/**
 * Class MockPayment to use PaymentModule
 */
class MockPayment extends PaymentModule
{
    public $active = 1;
    public $name = 'mock_payment';
    public $displayName = 'mock_payment';
}

/**
 * Class OrderHelper.
 */
class OrderHelper
{
    public static function createOrder()
    {
        $address = new Address();
        $address->id_country = AddressUtil::getCountryIdFromIso('fr');
        $address->alias = 'test address';
        $address->lastname = 'snow';
        $address->firstname = 'jon';
        $address->address1 = 'House Stark';
        $address->address2 = 'Winterfell';
        $address->city = 'Paris';
        $address->postcode = '75009';
        $address->phone = '0112341234';
        $address->phone_mobile = '0612341234';
        $address->save();

        $customer = new Customer();
        $customer->lastname = 'snow';
        $customer->firstname = 'jon';
        $customer->email = 'jsnow@boxtal.com';
        $customer->passwd = 'ghost';
        $customer->save();

        $cart = new Cart(null, 1);
        $cart->id_currency = Currency::getDefaultCurrency()->id;
        $cart->id_customer = $customer->id;
        $cart->id_address_delivery = $address->id;
        $cart->id_address_invoice = $address->id;
        $cart->id_shop = 1;
        $cart->id_shop_group = 1;
        $cart->id_lang = 1;
        $cart->save();
        Context::getContext()->cart = $cart;
        $productId = ProductHelper::createProduct();
        $cart->updateQty(1, $productId, null, false, 'up', $address->id);
        $delivery_option = array($address->id => '2,');
        $cart->setDeliveryOption($delivery_option);
        $cart->save();

        $payment = new MockPayment();
        $payment->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), 74.4, 'Mock payment', null, array(), null, false, false);
        $cart->delete();

        return $payment->currentOrder;
    }
}

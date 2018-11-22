<?php
/**
 * Order util tests
 */
use Boxtal\BoxtalConnectPrestashop\Util\OrderUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestOrderUtil.
 */
class TestOrderUtil extends TestCase
{
    public static $stack;

    /**
     * Setup at class instantiation.
     */
    public static function setUpBeforeClass()
    {
        $orderId = OrderHelper::createOrder();
        self::$stack = array();
        self::$stack['orderId'] = $orderId;
    }

    /**
     * Test getOrders function.
     */
    public function testGetOrders()
    {
        $orders = OrderUtil::getOrders();
        $this->assertSame($orders[0], array(
            'id_order' => '' . self::$stack['orderId'],
            'reference' => OrderUtil::getOrderReference(self::$stack['orderId']),
            'firstname' => 'jon',
            'lastname' => 'snow',
            'company' => '',
            'address1' => 'House Stark',
            'address2' => 'Winterfell',
            'city' => 'Paris',
            'postcode' => '75009',
            'country_iso' => 'FR',
            'state_iso' => null,
            'email' => 'jsnow@boxtal.com',
            'phone' => '0112341234',
            'phone_mobile' => '0612341234',
            'status' => 'Payment accepted',
            'shippingMethod' => 'My carrier',
            'shippingAmount' => '7.000000',
            'creationDate' => $orders[0]['creationDate'],
            'orderAmount' => $orders[0]['orderAmount'],
        ));
    }

    /**
     * Test getItemsFromOrder function.
     */
    public function testGetItemsFromOrder()
    {
        $items = OrderUtil::getItemsFromOrder(self::$stack['orderId']);
        $this->assertSame($items, array(
            0 => array(
                'product_id' => $items[0]['product_id'],
                'product_weight' => '0.000000',
                'product_price' => '55.000000',
                'product_quantity' => '1',
                'product_name' => 'Boxtal test product',
            ),
        ));
    }

    /**
     * Test getStatusMultilingual function.
     */
    public function testGetStatusMultilingual()
    {
        $status = OrderUtil::getStatusMultilingual(self::$stack['orderId']);
        $this->assertEquals($status['en_us'], 'Payment accepted');
    }

    /**
     * Test getStatusId function.
     */
    public function testGetStatusId()
    {
        $statusId = OrderUtil::getStatusId(self::$stack['orderId']);
        $this->assertEquals($statusId, 2);
    }

    /**
     * Test getCarrierId function.
     */
    public function testGetCarrierId()
    {
        $carrierId = OrderUtil::getCarrierId(self::$stack['orderId']);
        $this->assertEquals($carrierId, 2);
    }

    /**
     * Test getOrderStatuses function.
     */
    public function testGetOrderStatuses()
    {
        $statuses = OrderUtil::getOrderStatuses(1);
        $this->assertSame($statuses[0], array(
            'id_order_state' => '1',
            'name' => 'Awaiting check payment',
        ));
    }
}

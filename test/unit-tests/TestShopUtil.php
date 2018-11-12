<?php
/**
 * Shop util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestShopUtil.
 */
class TestShopUtil extends TestCase
{

    /**
     * Test getShopUrl function.
     */
    public function testGetShopUrl()
    {
        $this->assertEquals(ShopUtil::getShopUrl(1, 1), 'http://localhost/');

        if (true === ShopUtil::$multistore) {
            $this->assertEquals(ShopUtil::getShopUrl(2, 2), 'http://localhost/alternate/');
        }
    }
}

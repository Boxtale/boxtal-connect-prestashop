<?php
/**
 * Cart storage util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\CartStorageUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestCartStorageUtil.
 */
class TestCartStorageUtil extends TestCase
{

    /**
     * Test get set delete functions.
     */
    public function testGetSetDelete()
    {
        CartStorageUtil::set(10, 'key', 'value');
        $this->assertEquals(CartStorageUtil::get(10, 'key'), 'value');
        CartStorageUtil::delete(10);
        $this->assertNull(CartStorageUtil::get(10, 'key'));
    }
}

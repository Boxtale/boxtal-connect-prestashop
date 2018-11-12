<?php
/**
 * Order storage util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\OrderStorageUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestOrderStorageUtil.
 */
class TestOrderStorageUtil extends TestCase
{

    /**
     * Test get set functions.
     */
    public function testGetSet()
    {
        OrderStorageUtil::set(10, 'key', 'value');
        $this->assertEquals(OrderStorageUtil::get(10, 'key'), 'value');
        $this->assertNull(OrderStorageUtil::get(11, 'key'));
    }
}

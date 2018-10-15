<?php
/**
 * Misc util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\MiscUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestMiscUtil.
 */
class TestMiscUtil extends TestCase
{

    /**
     * Test isSetOrNull function.
     */
    public function testIsSetOrNull()
    {
        $test = array('testKey' => 'testValue');
        $this->assertEquals(MiscUtil::isSetOrNull($test, 'testKey'), 'testValue');
        $this->assertEquals(MiscUtil::isSetOrNull($test, 'wrongKey'), null);
    }

    /**
     * Test notEmptyOrNull function.
     */
    public function testNotEmptyOrNull()
    {
        $test = array(
            'testKey' => 'testValue',
            'testKey2' => '',
        );
        $this->assertEquals(MiscUtil::notEmptyOrNull($test, 'testKey'), 'testValue');
        $this->assertEquals(MiscUtil::notEmptyOrNull($test, 'wrongKey'), null);
        $this->assertEquals(MiscUtil::notEmptyOrNull($test, 'testKey2'), null);
    }
}

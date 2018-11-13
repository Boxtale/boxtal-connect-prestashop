<?php
/**
 * Environment util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\EnvironmentUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestEnvironmentUtil.
 */
class TestEnvironmentUtil extends TestCase
{

    /**
     * Test checkErrors function.
     */
    public function testCheckErrors()
    {
        $plugin = new MockPlugin();
        $plugin->minPhpVersion = '9.0';
        $this->assertNotFalse(EnvironmentUtil::checkErrors($plugin));
    }
}

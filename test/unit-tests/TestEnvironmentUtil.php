<?php
/**
 * Environment util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\EnvironmentUtil;
use PHPUnit\Framework\TestCase;

class mockPlugin
{
    public $minPhpVersion;

    function l($arg)
    {
        return $arg;
    }
}


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
        $plugin = new mockPlugin();
        $plugin->minPhpVersion = '9.0';
        $this->assertNotFalse(EnvironmentUtil::checkErrors($plugin));
    }
}

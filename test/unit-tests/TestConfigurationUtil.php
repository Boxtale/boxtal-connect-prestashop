<?php
/**
 * Configuration util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestConfigurationUtil.
 */
class TestConfigurationUtil extends TestCase
{

    /**
     * Test set and get functions.
     */
    public function testSetGet()
    {
        ConfigurationUtil::set('test', 'value');
        $this->assertEquals(ConfigurationUtil::get('test'), 'value');
    }
}

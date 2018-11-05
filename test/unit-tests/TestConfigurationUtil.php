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

    /**
     * Test get function.
     */
    public function testGet()
    {
        $this->assertNull(ConfigurationUtil::get('test2'));
    }

    /**
     * Test hasConfiguration function.
     */
    public function testHasConfiguration()
    {
        \Configuration::deleteByName('BX_MAP_BOOTSTRAP_URL');
        \Configuration::deleteByName('BX_MAP_TOKEN_URL');
        \Configuration::deleteByName('BX_PP_NETWORKS');
        ConfigurationUtil::set('BX_MAP_BOOTSTRAP_URL', 'value');
        ConfigurationUtil::set('BX_MAP_TOKEN_URL', 'value');
        $this->assertFalse(ConfigurationUtil::hasConfiguration(null, null));
        ConfigurationUtil::set('BX_PP_NETWORKS', 'value');
        $this->assertTrue(ConfigurationUtil::hasConfiguration(null, null));
    }

    /**
     * Test hasConfiguration function.
     */
    public function testGetOnboardingLink()
    {
        $this->assertEquals(ConfigurationUtil::getOnboardingLink(null, null), 'https://www.boxtal.build/onboarding?acceptLanguage=en&email=admin%40boxtal.com&shopType=prestashop');
    }
}

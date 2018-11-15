<?php
/**
 * Configuration util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
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
        $ppNetworks = ConfigurationUtil::get('BX_PP_NETWORKS');
        ConfigurationUtil::delete('BX_PP_NETWORKS');
        $this->assertFalse(ConfigurationUtil::hasConfiguration(ShopUtil::$shopGroupId, ShopUtil::$shopId));
        ConfigurationUtil::set('BX_PP_NETWORKS', $ppNetworks);
        var_dump(ConfigurationUtil::get('BX_MAP_BOOTSTRAP_URL'));
        var_dump(ConfigurationUtil::get('BX_MAP_TOKEN_URL'));
        var_dump(ConfigurationUtil::get('BX_MAP_LOGO_IMAGE_URL'));
        var_dump(ConfigurationUtil::get('BX_MAP_LOGO_HREF_URL'));
        var_dump(ConfigurationUtil::get('BX_PP_NETWORKS'));
        var_dump(ConfigurationUtil::get('BX_TRACKING_URL_PATTERN'));
        $this->assertTrue(ConfigurationUtil::hasConfiguration(ShopUtil::$shopGroupId, ShopUtil::$shopId));
    }

    /**
     * Test hasConfiguration function.
     */
    public function testGetOnboardingLink()
    {
        $this->assertEquals(ConfigurationUtil::getOnboardingLink(null, null), 'https://www.boxtal.build/onboarding?acceptLanguage=en&email=admin%40boxtal.com&shopType=prestashop');
    }
}

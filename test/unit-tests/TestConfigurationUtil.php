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
     * Test set, get and delete functions.
     */
    public function testSetGetDelete()
    {
        ConfigurationUtil::set('test', 'value');
        $this->assertEquals(ConfigurationUtil::get('test'), 'value');
        ConfigurationUtil::delete('test', ShopUtil::$shopGroupId, ShopUtil::$shopId);
        \Configuration::clearConfigurationCacheForTesting();
        $this->assertNull(ConfigurationUtil::get('test'));
    }

    /**
     * Test get function when value is not set.
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
        ConfigurationUtil::deleteAllShops('BX_PP_NETWORKS');
        $this->assertFalse(ConfigurationUtil::hasConfiguration(ShopUtil::$shopGroupId, ShopUtil::$shopId));
        ConfigurationUtil::set('BX_PP_NETWORKS', $ppNetworks);
        $this->assertTrue(ConfigurationUtil::hasConfiguration(ShopUtil::$shopGroupId, ShopUtil::$shopId));
    }

    /**
     * Test getOnboardingLink function.
     */
    public function testGetOnboardingLink()
    {
        $this->assertEquals(ConfigurationUtil::getOnboardingLink(ShopUtil::$shopGroupId, ShopUtil::$shopId), 'https://www.boxtal.build/onboarding?acceptLanguage=en&email=admin%40boxtal.com&shopUrl=http%3A%2F%2Flocalhost%2F&shopType=prestashop');
    }
}

<?php
/**
 * Auth util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestAuthUtil.
 */
class TestAuthUtil extends TestCase
{

    /**
     * Test isPluginPaired function.
     */
    public function testIsPluginPaired()
    {
        $accessKey = AuthUtil::getAccessKey(ShopUtil::$shopGroupId, ShopUtil::$shopId);
        $secretKey = AuthUtil::getSecretKey(ShopUtil::$shopGroupId, ShopUtil::$shopId);
        \Configuration::deleteByName('BX_ACCESS_KEY');
        \Configuration::deleteByName('BX_SECRET_KEY');
        $this->assertFalse(AuthUtil::isPluginPaired(ShopUtil::$shopGroupId, ShopUtil::$shopId));
        AuthUtil::pairPlugin($accessKey, $secretKey);
        $this->assertTrue(AuthUtil::isPluginPaired(ShopUtil::$shopGroupId, ShopUtil::$shopId));
    }

    /**
     * Test canUsePlugin function.
     */
    public function testCanUsePlugin()
    {
        ConfigurationUtil::set('BX_PAIRING_UPDATE', 'test');
        $this->assertFalse(AuthUtil::canUsePlugin());
        \Configuration::deleteByName('BX_PAIRING_UPDATE');
        $this->assertTrue(AuthUtil::canUsePlugin());
    }

    /**
     * Test pairing update.
     */
    public function testPairingUpdate()
    {
        $this->assertNull(ConfigurationUtil::get('BX_PAIRING_UPDATE'));
        AuthUtil::startPairingUpdate('test');
        $this->assertEquals(ConfigurationUtil::get('BX_PAIRING_UPDATE'), 'test');
        AuthUtil::endPairingUpdate();
    }
}

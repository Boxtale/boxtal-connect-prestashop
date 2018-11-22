<?php
/**
 * Shipping method util tests
 */
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestShippingMethodUtil.
 */
class TestShippingMethodUtil extends TestCase
{
    /**
     * Test get set selected parcel point networks function.
     */
    public function testParcelPointNetworks()
    {
        $parcelPointNetworks = array('SOGP_NETWORK', 'MONR_NETWORK');
        ShippingMethodUtil::setSelectedParcelPointNetworks(1, $parcelPointNetworks);
        $this->assertSame(ShippingMethodUtil::getSelectedParcelPointNetworks(1), $parcelPointNetworks);
        $this->assertSame(ShippingMethodUtil::getAllSelectedParcelPointNetworks(), $parcelPointNetworks);
    }

    /**
     * Test getCleanId function.
     */
    public function testGetCleanId()
    {
        $this->assertEquals(ShippingMethodUtil::getCleanId('1,'), 1);
    }
}

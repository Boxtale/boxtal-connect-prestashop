<?php
/**
 * Address util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\AddressUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestAddressUtil.
 */
class TestAddressUtil extends TestCase
{

    /**
     * Test convert function.
     */
    public function testConvert()
    {
        $address = new stdClass();
        $address->address1 = 'address1';
        $address->address2 = 'address2';
        $address->city = 'city';
        $address->postcode = 'postcode';
        $address->id_country = '8';
        $address->id_state = '10';
        $this->assertSame(AddressUtil::convert($address), array(
            'street' => 'address1 address2',
            'city' => 'city',
            'zipCode' => 'postcode',
            'country' => AddressUtil::getCountryIsoFromId('8'),
            'state' => AddressUtil::getStateIsoFromId('10'),
        ));
    }

    /**
     * Test getCountryIsoFromId function.
     */
    public function testGetCountryIsoFromId()
    {
        $this->assertNotNull(AddressUtil::getCountryIsoFromId('8'));
    }

    /**
     * Test getStateIsoFromId function.
     */
    public function testGetStateIsoFromId()
    {
        $this->assertNotNull(AddressUtil::getStateIsoFromId('10'));
    }
}

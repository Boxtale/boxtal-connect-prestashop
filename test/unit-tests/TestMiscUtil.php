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
        $this->assertNull(MiscUtil::isSetOrNull($test, 'wrongKey'));
    }

    /**
     * Test propertyExistsOrNull function.
     */
    public function testPropertyExistsOrNull()
    {
        $test = new stdClass();
        $this->assertNull(MiscUtil::propertyExistsOrNull($test, 'property1'));
        $test->property1 = 'value';
        $this->assertEquals(MiscUtil::propertyExistsOrNull($test, 'property1'), 'value');
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
        $this->assertNull(MiscUtil::notEmptyOrNull($test, 'wrongKey'));
        $this->assertNull(MiscUtil::notEmptyOrNull($test, 'testKey2'));
    }

    /**
     * Test toFloatOrNull function.
     */
    public function testToFloatOrNull()
    {
        $test1 = '7.1';
        $this->assertEquals(MiscUtil::toFloatOrNull($test1), 7.1);
        $this->assertNull(MiscUtil::toFloatOrNull(null));
    }

    /**
     * Test base64OrNull function.
     */
    public function testBase64OrNull()
    {
        $test = 'test';
        $this->assertEquals(MiscUtil::base64OrNull($test), 'dGVzdA==');
        $this->assertNull(MiscUtil::base64OrNull(null));
    }

    /**
     * Test convertStdClassToArray function.
     */
    public function testConvertStdClassToArray()
    {
        $test = new stdClass();
        $test->property1 = 'value1';
        $test->property2 = ['value2'];
        $test->property3 = new stdClass();
        $test->property3->subproperty = 'value3';
        $this->assertSame(MiscUtil::convertStdClassToArray($test), array(
            'property1' => 'value1',
            'property2' => array('value2'),
            'property3' => array('subproperty' => 'value3'),
        ));
    }

    /**
     * Test dateW3Cformat function.
     */
    public function testDateW3Cformat()
    {
        $test = '2018-11-02 15:03';
        $this->assertEquals(MiscUtil::dateW3Cformat($test), '2018-11-02T15:03:00+01:00');
    }

    /**
     * Test getFirstAdminUserEmail function.
     */
    public function testGetFirstAdminUserEmail()
    {
        $this->assertEquals(MiscUtil::getFirstAdminUserEmail(), 'admin@boxtal.com');
    }
}

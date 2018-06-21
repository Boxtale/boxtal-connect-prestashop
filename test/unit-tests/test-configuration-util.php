<?php
/**
 * Configuration util tests
 */

use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;
use PHPUnit\Framework\TestCase;


/**
 * Class BWTestConfigurationUtil.
 */
class BWTestConfigurationUtil extends TestCase {

	/**
	 * Test set and get functions.
	 */
	public function testSetGet() {
        ConfigurationUtil::set('test', 'value');
        $this->assertEquals( ConfigurationUtil::get('test'), 'value' );
	}
}

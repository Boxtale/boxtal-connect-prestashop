<?php
/**
 * Product util tests
 */

use Boxtal\BoxtalConnectPrestashop\Util\ProductUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TestProductUtil.
 */
class TestProductUtil extends TestCase
{

    /**
     * Test getProductDescriptionMultilingual function.
     */
    public function testGetProductDescriptionMultilingual()
    {
        $productId = ProductHelper::createProduct();
        $this->assertSame(ProductUtil::getProductDescriptionMultilingual($productId), array(
            'en_us' => 'Boxtal test product',
        ));
    }

    protected function tearDown()
    {
        $this->stack = [];
    }
}

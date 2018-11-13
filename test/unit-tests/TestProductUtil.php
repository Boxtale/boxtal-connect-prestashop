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
        $productDescriptionMultilingual = ProductUtil::getProductDescriptionMultilingual($productId);
        $this->assertEquals($productDescriptionMultilingual['en_us'], 'Boxtal test product');
    }
}

<?php
/**
 * Test product helper
 */

/**
 * Class ProductHelper.
 */
class ProductHelper
{
    /**
     * Create a product.
     *
     * @return int product id
     */
    public static function createProduct()
    {
        $product = new Product(null, false, Configuration::get('PS_LANG_DEFAULT'));
        $rand = rand();
        $name = 'Boxtal test product';
        $product->name = $name;
        $product->price = 55;
        //phpcs:disable
        $product->link_rewrite = Tools::link_rewrite($name);
        $product->id_supplier = 1;
        $product->id_manufacturer = 1;
        $product->id_category_default = 5;
        //phpcs:enable
        $product->reference = 'box' . $rand;
        $product->save();
        StockAvailable::setQuantity($product->id, 0, 10);

        return (int) $product->id;
    }
}

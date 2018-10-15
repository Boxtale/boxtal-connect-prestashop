<?php
/**
 * Contains code for product util class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Util;

/**
 * Product util class.
 *
 * Helper to manage products.
 */
class ProductUtil
{

    /**
     * Get product multilingual description.
     *
     * @param int $productId product id
     *
     * @return array
     */
    public static function getProductDescriptionMultilingual($productId)
    {
        $product = new \Product($productId);

        if (!is_object($product) || !is_array($product->name)) {
            return array();
        }

        $translations = array();
        foreach ($product->name as $langId => $productName) {
            $sql = new \DbQuery();
            $sql->select('l.locale');
            $sql->from('lang', 'l');
            $sql->where('l.id_lang = '.$langId);
            $result = \Db::getInstance()->executeS($sql);
            $row = array_shift($result);
            $translations[$row['locale']] = $productName;
        }

        return $translations;
    }
}

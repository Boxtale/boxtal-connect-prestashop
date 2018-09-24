<?php
/**
 * Contains code for shop util class.
 */

namespace Boxtal\BoxtalPrestashop\Util;

use Boxtal;
use Boxtal\BoxtalPhp\ApiClient;
use Boxtal\BoxtalPhp\RestClient;
use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;

/**
 * Shop util class.
 *
 * Helper to manage shops.
 */
class ShopUtil
{

    /**
     * Get current shop id.
     *
     * @return string shop id.
     */
    public static function getCurrentShop()
    {
        if (isset(\Context::getContext()->shop)) {
            $idShop = (int)\Context::getContext()->shop->id;
        }
        if (!$idShop) {
            $idShop = (int)\Configuration::get('PS_SHOP_DEFAULT');
        }
        return $idShop;
    }
}

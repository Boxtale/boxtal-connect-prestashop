<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Boxtal <api@boxtal.com>
 * @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for the display order detail controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Hook;

use Boxtal\BoxtalConnectPrestashop\Util\ParcelPointUtil;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;

/**
 * Display order detail controller class.
 *
 * Generate the content to display on DisplayOrderDetails hook
 *
 * @class DisplayOrderDetailController
 */
class DisplayOrderDetailController
{
    private static $templateFile = 'hook/hookDisplayOrderDetail.tpl';


    /**
     * Generate the content to display on DisplayOrderDetail hook
     *
     * @param mixed $params
     *
     * @return string extra content to display
     */
    public static function trigger($params)
    {
        if (!AuthUtil::canUsePlugin()) {
            return null;
        }

        $parcelpoint = ParcelPointUtil::getOrderParcelPoint((int) $params['order']->id);

        $isDisplayableParcelPoint = null !== $parcelpoint
            && !empty($parcelpoint->name)
            && !empty($parcelpoint->address)
            && !empty($parcelpoint->city)
            && !empty($parcelpoint->zipcode)
            && !empty($parcelpoint->country);

        if (!$isDisplayableParcelPoint) {
            return null;
        }

        $smarty = \BoxtalConnect::getInstance()->getSmarty();
        $smarty->assign('parcelpoint', $parcelpoint);

        return \BoxtalConnect::getInstance()->displayTemplate(static::$templateFile);
    }
}

<?php
/**
 * 2007-2018 PrestaShop
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
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Custom notice rendering
 */
$classes = '';
switch ($notice->status) {
    case 'warning':
        $classes .= 'module_error alert alert-danger';
        break;

    case 'info':
        $classes .= 'module_warning alert alert-warning';
        break;

    case 'success':
        $classes .= 'module_confirmation conf confirm alert alert-success';
        break;

    default:
        break;
}
?>
<div class="<?php echo $classes; ?>">
    <?php echo sprintf($boxtalconnect->l('%s: %s'), $shopName, $notice->message); ?>
    <p>
        <a class="bx-hide-notice" data-key="<?php echo $notice->key; ?>"
           data-shop-group-id="<?php echo $notice->shopGroupId; ?>" data-shop-id="<?php echo $notice->shopId; ?>">
            <?php echo $boxtalconnect->l('Hide this notice'); ?>
        </a>
    </p>
</div>

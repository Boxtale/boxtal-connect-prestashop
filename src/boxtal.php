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
 * @author    EnvoiMoinsCher <api@boxtal.com>
 *
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 *
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 */

namespace Boxtal\BoxtalPrestashop;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Boxtal
 *
 *  Main module class.
 */
class Boxtal extends Module
{

    /**
     * Construct function.
     *
     * @void
     */
    public function __construct()
    {
        $this->name = 'boxtal';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Boxtal';
        //phpcs:ignore
        $this->need_instance = 0;
        //phpcs:ignore
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Boxtal');
        $this->description = $this->l('Ship your orders with multiple carriers and save up to 75% on your shipping costs without commitments or any contracts.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }
}

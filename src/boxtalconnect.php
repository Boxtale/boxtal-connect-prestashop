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
 *
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 *
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

use Boxtal\BoxtalConnectPrestashop\Controllers\Front\ParcelPointController;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\TrackingController;
use Boxtal\BoxtalConnectPrestashop\Init\EnvironmentCheck;
use Boxtal\BoxtalConnectPrestashop\Init\SetupWizard;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\EnvironmentUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once  __DIR__.'/autoloader.php';

/**
 * Class boxtalconnect
 *
 *  Main module class.
 */
class boxtalconnect extends Module
{

    /**
     * Instance.
     *
     * @var boxtalconnect
     */
    private static $instance;

    /**
     * Construct function.
     *
     * @void
     */
    public function __construct()
    {
        $this->name = 'boxtalconnect';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Boxtal';
        //phpcs:ignore
        $this->need_instance = 0;
        //phpcs:ignore
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->file = __FILE__;
        $this::$instance = $this;
        parent::__construct();

        $this->displayName = $this->l('Boxtal Connect');
        $this->description = $this->l('Managing your shipments becomes easier with our free plugin Boxtal! Save time and enjoy negotiated rates with 15 carriers: Colissimo, Mondial Relay...');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->minPhpVersion = '5.3.0';
        $this->onboardingUrl = 'https://www.boxtal.com/onboarding';

        ShopUtil::getShopContext();

        if ($this->active) {
            $this->initEnvironmentCheck($this);

            if (false === EnvironmentUtil::checkErrors($this)) {
                $this->initSetupWizard($this);
                $this->initShopController($this);
                $this->initAdminAjaxController($this);

                if (AuthUtil::canUsePlugin()) {
                    $this->initFrontAjaxController($this);
                    $this->initOrderController($this);
                }
            }
        }
    }

    /**
     * Install function.
     *
     * @return boolean
     */
    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('header')
            || !$this->registerHook('displayCarrierList')
            || !$this->registerHook('displayAfterCarrier')
            || !$this->registerHook('newOrder')
            || !$this->registerHook('updateCarrier')
            || !$this->registerHook('adminOrder')
            || !$this->registerHook('displayAdminAfterHeader')) {
            return false;
        }

        \Db::getInstance()->execute(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."bx_notices` (
            `id_notice` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_shop_group` int(11) unsigned NULL,
            `id_shop` int(11) unsigned NULL,
            `key` varchar(255) NOT NULL,
            `value` text,
            PRIMARY KEY (`id_notice`),
            CONSTRAINT UC_bx_notices UNIQUE (`key`, `id_shop_group`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8"
        );

        \Db::getInstance()->execute(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."bx_carrier` (
            `id_bx_carrier` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_carrier` int(10) unsigned NOT NULL,
            `id_shop_group` int(11) unsigned NULL,
            `id_shop` int(11) unsigned NULL,
            `parcel_point_networks` text,
            PRIMARY KEY (`id_bx_carrier`),
            CONSTRAINT UC_bx_carrier UNIQUE (`id_carrier`, `id_shop_group`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8"
        );

        \Db::getInstance()->execute(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."bx_cart_storage` (
            `id_cart_storage` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_cart` int(10) unsigned NOT NULL,
            `id_shop_group` int(11) unsigned NULL,
            `id_shop` int(11) unsigned NULL,
            `key` varchar(255) NOT NULL,
            `value` mediumtext,
            PRIMARY KEY (`id_cart_storage`),
            CONSTRAINT UC_bx_cart_storage UNIQUE (`id_cart`, `id_shop_group`, `id_shop`, `key`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8"
        );

        \Db::getInstance()->execute(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."bx_order_storage` (
            `id_order_storage` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_order` int(10) unsigned NOT NULL,
            `id_shop_group` int(11) unsigned NULL,
            `id_shop` int(11) unsigned NULL,
            `key` varchar(255) NOT NULL,
            `value` varchar(255),
            PRIMARY KEY (`id_order_storage`),
            CONSTRAINT UC_bx_order_storage UNIQUE (`id_order`, `id_shop_group`, `id_shop`, `key`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8"
        );

        // add invisible tab for admin ajax controller
        $invisibleTab = new \Tab();
        $invisibleTab->active = 1;
        //phpcs:ignore
        $invisibleTab->class_name = 'AdminAjax';
        $invisibleTab->name = array();
        foreach (\Language::getLanguages(true) as $lang) {
            $invisibleTab->name[$lang['id_lang']] = 'Ajax route';
        }
        //phpcs:ignore
        $invisibleTab->id_parent = -1;
        $invisibleTab->module = $this->name;
        if (false === $invisibleTab->add()) {
            return false;
        }

        // add the new tab
        $tab = new Tab();
        //phpcs:ignore
        $tab->class_name = 'AdminShippingMethod';
        //phpcs:ignore
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentShipping');
        $tab->module = $this->name;
        $tab->name = array();
        foreach (\Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Boxtal Connect';
        }
        if (false === $tab->add()) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall function.
     *
     * @return boolean
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        $shops = ShopUtil::getShops();
        foreach ($shops as $shop) {
            ConfigurationUtil::deleteConfiguration($shop['id_shop_group'], $shop['id_shop']);
        }
        \DB::getInstance()->execute(
            'SET FOREIGN_KEY_CHECKS = 0;
            DROP TABLE IF EXISTS `'._DB_PREFIX_.'bx_notices`;
            DROP TABLE IF EXISTS `'._DB_PREFIX_.'bx_carrier`;
            DROP TABLE IF EXISTS `'._DB_PREFIX_.'bx_cart_storage`;
            DROP TABLE IF EXISTS `'._DB_PREFIX_.'bx_order_storage`;
            DELETE FROM `'._DB_PREFIX_.'configuration` WHERE name like "BX_%";
            SET FOREIGN_KEY_CHECKS = 1;'
        );

        return true;
    }

    /**
     * Get module instance.
     *
     * @return boxtalconnect
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * DisplayBackOfficeHeader hook. Used to display relevant css & js.
     *
     * @void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $controller = $this->getContext()->controller;

        if (NoticeController::hasNotices()) {
            if (method_exists($controller, 'registerJavascript')) {
                $controller->registerJavascript(
                    'bx-notices',
                    'modules/'.$this->name.'/views/css/notices.min.js',
                    array('priority' => 100, 'server' => 'local')
                );
            } else {
                $controller->addJs('modules/'.$this->name.'/views/js/notices.min.js');
            }
            if (method_exists($controller, 'registerStylesheet')) {
                $controller->registerStylesheet(
                    'bx-notices',
                    'modules/'.$this->name.'/views/css/notices.css',
                    array('priority' => 100, 'server' => 'local')
                );
            } else {
                $controller->addCSS('modules/'.$this->name.'/views/css/notices.css', 'all');
            }
        }

        if ('AdminOrdersController' === get_class($controller) && false !== Tools::getValue('id_order')) {
            if (method_exists($controller, 'registerStylesheet')) {
                $controller->registerStylesheet(
                    'bx-tracking',
                    'modules/'.$this->name.'/views/css/tracking.css',
                    array('priority' => 100, 'server' => 'local')
                );
            } else {
                $controller->addCSS('modules/'.$this->name.'/views/css/tracking.css', 'all');
            }
        }
    }

    /**
     * Header hook. Display includes JavaScript for maps.
     *
     * @param mixed $params context values
     *
     * @return string html
     */
    public function hookHeader($params)
    {
        if (!AuthUtil::canUsePlugin()) {
            return null;
        }

        return ParcelPointController::addScripts();
    }

    /**
     * Prestashop < 1.7. Used to display front-office relay point list.
     *
     * @param array $params Parameters array (cart object, address information)
     *
     * @return string html
     */
    public function hookDisplayCarrierList($params)
    {
        if (!AuthUtil::canUsePlugin()) {
            return null;
        }

        return ParcelPointController::initPoints($params);
    }

    /**
     * Prestashop > 1.7. Used to display front-office relay point list.
     *
     * @param array $params Parameters array (cart object, address information)
     *
     * @return string html
     */
    public function hookDisplayAfterCarrier($params)
    {
        if (!AuthUtil::canUsePlugin()) {
            return null;
        }

        return ParcelPointController::initPoints($params);
    }

    /**
     * Order creation hook.
     *
     * @param array $params list of order params.
     *
     * @void
     */
    public function hooknewOrder($params)
    {
        if (!AuthUtil::canUsePlugin()) {
            return;
        }

        ParcelPointController::orderCreated($params);
    }

    /**
     * Update carrier hook. Used to update carrier id.
     *
     * @param array $params List of params used in the operation.
     *
     * @void
     */
    public function hookUpdateCarrier($params)
    {
        $idCarrierOld = (int) $params['id_carrier'];
        $idCarrierNew = (int) $params['carrier']->id;

        $data = array('id_carrier' => $idCarrierNew);
        \Db::getInstance()->update(
            'bx_carrier',
            $data,
            'id_carrier = '.$idCarrierOld,
            0,
            true
        );
    }

    /**
     * DisplayAdminAfterHeader hook. Used to display notices.
     *
     * @void
     */
    public function hookDisplayAdminAfterHeader()
    {
        $notices = NoticeController::getNoticeInstances();
        foreach ($notices as $notice) {
            $notice->render();
        }
    }

    /**
     * adminOrder hook. Used to display tracking in admin orders.
     *
     * @param array $params List of params used in the operation.
     *
     * @return string html
     */
    public function hookAdminOrder($params)
    {

        if (!AuthUtil::canUsePlugin()) {
            return null;
        }

        $tracking = TrackingController::getOrderTracking($params['id_order']);
        if (null === $tracking || ! property_exists($tracking, 'shipmentsTracking') || empty($tracking->shipmentsTracking)) {
            return null;
        }

        $smarty = $this->getSmarty();
        $smarty->assign('tracking', $tracking);
        $smarty->assign('dateFormat', $this->l('Y-m-d H:i:s'));

        return $this->display(__FILE__, '/views/templates/hook/hookAdminOrder.tpl');
    }

    /**
     * Get context.
     *
     * @return \Context context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Check PHP version.
     *
     * @param boxtalconnect $plugin plugin array.
     *
     * @return EnvironmentCheck $object static environment check instance.
     */
    public function initEnvironmentCheck($plugin)
    {
        static $object;

        if (null !== $object) {
            return $object;
        }

        $object =  new EnvironmentCheck($plugin);

        return $object;
    }

    /**
     * Init setup wizard.
     *
     * @param boxtalconnect $plugin plugin array.
     *
     * @return SetupWizard $object static setup wizard instance.
     */
    public function initSetupWizard($plugin)
    {
        static $object;

        if (null !== $object) {
            return $object;
        }

        $object =  new SetupWizard($plugin);

        return $object;
    }

    /**
     * Init shop controller.
     *
     * @param boxtalconnect $plugin plugin array.
     *
     * @void
     */
    public function initShopController($plugin)
    {
        require_once __DIR__.'/controllers/front/shop.php';
    }

    /**
     * Init admin ajax controller.
     *
     * @param boxtalconnect $plugin plugin array.
     *
     * @void
     */
    public function initAdminAjaxController($plugin)
    {
        require_once __DIR__.'/controllers/admin/AdminAjaxController.php';
    }

    /**
     * Init front ajax controller.
     *
     * @param boxtalconnect $plugin plugin array.
     *
     * @void
     */
    public function initFrontAjaxController($plugin)
    {
        if (!AuthUtil::canUsePlugin()) {
            return;
        }

        require_once __DIR__.'/controllers/front/ajax.php';
    }

    /**
     * Init order controller.
     *
     * @param boxtalconnect $plugin plugin array.
     *
     * @void
     */
    public function initOrderController($plugin)
    {
        if (!AuthUtil::canUsePlugin()) {
            return;
        }

        require_once __DIR__.'/controllers/front/order.php';
    }


    /**
     * Get smarty.
     *
     * @return object
     */
    public function getSmarty()
    {
        return $this->getContext()->smarty;
    }

    /**
     * Get current controller.
     *
     * @return object
     */
    public function getCurrentController()
    {
        return $this->getContext()->controller;
    }

    /**
     * Display template.
     *
     * @param string $templatePath path to template from module folder.
     *
     * @return string html
     */
    public function displayTemplate($templatePath)
    {
        return $this->display(__FILE__, '/views/templates/'.$templatePath);
    }
}

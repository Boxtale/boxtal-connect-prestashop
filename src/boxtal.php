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

use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once  __DIR__.'/autoloader.php';

/**
 * Class Boxtal
 *
 *  Main module class.
 */
class Boxtal extends Module
{

    /**
     * Instance.
     *
     * @var Boxtal
     */
    private static $instance;

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
        $this->file = __FILE__;
        $this::$instance = $this;
        parent::__construct();

        $this->displayName = $this->l('Boxtal');
        $this->description = $this->l('Ship your orders with multiple carriers and save up to 75% on your shipping costs without commitments or any contracts.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if ($this->active) {
            if (!AuthUtil::isPluginPaired()) {
                NoticeController::addNotice('setupWizard');
            } else {
                NoticeController::removeNotice('setupWizard');
            }

            if (AuthUtil::canUsePlugin()) {
                require_once __DIR__.'/controllers/admin/ajax.php';
                require_once __DIR__.'/controllers/front/order.php';
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
            || !$this->registerHook('displayAdminAfterHeader')) {
            return false;
        }

        \Db::getInstance()->execute(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."bx_notices` (
            `id_notice` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_shop_group` int(11) unsigned,
            `id_shop` int(11) unsigned,
            `key` varchar(255) NOT NULL,
            `value` text,
            PRIMARY KEY (`id_notice`),
            UNIQUE (`key`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8"
        );

        return true;
    }

    /**
     * Uninstall function.
     *
     * @return void
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        ConfigurationUtil::delete('BX_PAIRING_UPDATE');
        ConfigurationUtil::delete('BX_ACCESS_KEY');
        ConfigurationUtil::delete('BX_SECRET_KEY');
        ConfigurationUtil::delete('BX_NOTICES');
        \DB::getInstance()->execute(
            'SET FOREIGN_KEY_CHECKS = 0;
            DROP TABLE IF EXISTS `'._DB_PREFIX_.'bx_notices`;
            DELETE FROM `'._DB_PREFIX_.'configuration` WHERE name like "BX_%";
            SET FOREIGN_KEY_CHECKS = 1;'
        );

        return true;
    }

    /**
     * Get module instance.
     *
     * @return Boxtal
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

        $notices = NoticeController::getNotices();
        if (! empty($notices)) {
            if (method_exists($controller, 'registerJavascript')) {
                $controller->registerJavascript(
                    'bx-notice',
                    'modules/boxtal/views/js/notice.min.js',
                    array('priority' => 100, 'server' => 'local')
                );
            } else {
                $controller->addJs(_MODULE_DIR_.'/boxtal/views/js/notices.min.js');
                $controller->addCSS(_MODULE_DIR_.'/boxtal/views/css/notices.css', 'all');
            }
        }
    }

    /**
     * DisplayAdminAfterHeader hook. Used to display notices.
     *
     * @void
     */
    public function hookDisplayAdminAfterHeader()
    {
        $notices = NoticeController::getNotices();
        foreach ($notices as $notice) {
            $notice->render();
        }
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
}

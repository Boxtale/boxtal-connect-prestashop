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
 * Contains code for the shipping method admin controller.
 */
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\OrderUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;

/**
 * Shipping method admin controller class.
 */
class AdminShippingMethodController extends \ModuleAdminController
{
    /**
     * Construct function.
     *
     * @void
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'AdminShippingMethodController';
        parent::__construct();
    }

    /**
     * Controller init.
     *
     * @void
     */
    public function init()
    {
        parent::init();
        if (\Tools::isSubmit('submitParcelPointNetworks')) {
            $this->handleParcelPointNetworksForm();
        }
        if (\Tools::isSubmit('submitTrackingEvents')) {
            $this->handleTrackingEventsForm();
        }
        $boxtalConnect = BoxtalConnect::getInstance();
        if (true === ShopUtil::$multistore && null === ShopUtil::$shopId) {
            $this->content = $boxtalConnect->displayTemplate('admin/multistoreAccessDenied.tpl');
            //phpcs:ignore
            return;
        } elseif (!AuthUtil::canUsePlugin()) {
            $this->content = $boxtalConnect->displayTemplate('admin/accessDenied.tpl');
            //phpcs:ignore
            return;
        }

        $smarty = $boxtalConnect->getSmarty();
        $parcelPointNetworks = @unserialize(ConfigurationUtil::get('BX_PP_NETWORKS'));
        $smarty->assign('parcelPointNetworks', $parcelPointNetworks);
        $carriers = ShippingMethodUtil::getShippingMethods();
        foreach ((array) $carriers as $c => $carrier) {
            if (file_exists(_PS_SHIP_IMG_DIR_ . (int) $carrier['id_carrier'] . '.jpg')) {
                $carriers[$c]['logo'] = _THEME_SHIP_DIR_ . (int) $carrier['id_carrier'] . '.jpg';
            }
            $carriers[$c]['parcel_point_networks'] = unserialize($carriers[$c]['parcel_point_networks']);
        }
        $smarty->assign('carriers', $carriers);

        //phpcs:ignore
        $langId = $boxtalConnect->getContext()->language->id;
        $orderStatuses = OrderUtil::getOrderStatuses($langId);
        $smarty->assign('orderStatuses', $orderStatuses);
        $orderPrepared = ConfigurationUtil::get('BX_ORDER_PREPARED');
        $orderShipped = ConfigurationUtil::get('BX_ORDER_SHIPPED');
        $orderDelivered = ConfigurationUtil::get('BX_ORDER_DELIVERED');

        if ('' !== $orderPrepared && null !== $orderPrepared) {
            $isValidOrderPrepared = false;
            foreach ($orderStatuses as $status) {
                if ($status['id_order_state'] === $orderPrepared) {
                    $isValidOrderPrepared = true;
                }
            }

            if (false === $isValidOrderPrepared) {
                $smarty->assign('orderPrepared', null);
                ConfigurationUtil::set('BX_ORDER_PREPARED', null);
            } else {
                $smarty->assign('orderPrepared', $orderPrepared);
            }
        } else {
            $smarty->assign('orderPrepared', $orderPrepared);
        }

        if ('' !== $orderShipped && null !== $orderShipped) {
            $isValidOrderShipped = false;
            foreach ($orderStatuses as $status) {
                if ($status['id_order_state'] === $orderShipped) {
                    $isValidOrderShipped = true;
                }
            }

            if (false === $isValidOrderShipped) {
                $smarty->assign('orderShipped', null);
                ConfigurationUtil::set('BX_ORDER_SHIPPED', null);
            } else {
                $smarty->assign('orderShipped', $orderShipped);
            }
        } else {
            $smarty->assign('orderShipped', $orderShipped);
        }

        if ('' !== $orderDelivered && null !== $orderDelivered) {
            $isValidOrderDelivered = false;
            foreach ($orderStatuses as $status) {
                if ($status['id_order_state'] === $orderDelivered) {
                    $isValidOrderDelivered = true;
                }
            }

            if (false === $isValidOrderDelivered) {
                $smarty->assign('orderDelivered', null);
                ConfigurationUtil::set('BX_ORDER_DELIVERED', null);
            } else {
                $smarty->assign('orderDelivered', $orderDelivered);
            }
        } else {
            $smarty->assign('orderDelivered', $orderDelivered);
        }

        $trackingUrlPattern = ConfigurationUtil::getTrackingUrlPattern();
        $helpCenterUrl = ConfigurationUtil::getHelpCenterUrl();
        $smarty->assign('trackingUrlPattern', str_replace('%s', '@', $trackingUrlPattern));
        $smarty->assign('helpCenterUrl', $helpCenterUrl);

        $this->content = $boxtalConnect->displayTemplate('admin/configuration/settings.tpl');
    }

    /**
     * Handle parcel point networks form.
     *
     * @void
     */
    private function handleParcelPointNetworksForm()
    {
        $carriers = ShippingMethodUtil::getShippingMethods();
        foreach ((array) $carriers as $carrier) {
            $parcelPointNetworks = \Tools::isSubmit('parcelPointNetworks_' . (int) $carrier['id_carrier']) ?
                \Tools::getValue('parcelPointNetworks_' . (int) $carrier['id_carrier']) : array();
            ShippingMethodUtil::setSelectedParcelPointNetworks((int) $carrier['id_carrier'], $parcelPointNetworks);
        }
    }

    /**
     * Handle tracking events form.
     *
     * @void
     */
    private function handleTrackingEventsForm()
    {

        if (\Tools::isSubmit('orderPrepared')) {
            $status = \Tools::getValue('orderPrepared');
            if ('' === $status) {
                ConfigurationUtil::set('BX_ORDER_PREPARED', null);
            } else {
                ConfigurationUtil::set('BX_ORDER_PREPARED', $status);
            }
        }

        if (\Tools::isSubmit('orderShipped')) {
            $status = \Tools::getValue('orderShipped');
            if ('' === $status) {
                ConfigurationUtil::set('BX_ORDER_SHIPPED', null);
            } else {
                ConfigurationUtil::set('BX_ORDER_SHIPPED', $status);
            }
        }

        if (\Tools::isSubmit('orderDelivered')) {
            $status = \Tools::getValue('orderDelivered');
            if ('' === $status) {
                ConfigurationUtil::set('BX_ORDER_DELIVERED', null);
            } else {
                ConfigurationUtil::set('BX_ORDER_DELIVERED', $status);
            }
        }
    }
}

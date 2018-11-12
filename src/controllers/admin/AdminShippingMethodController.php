<?php
/**
 * Contains code for the shipping method admin controller.
 */

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
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
        global $cookie;

        parent::init();
        if (\Tools::isSubmit('submitParcelPointNetworks')) {
            $this->handleParcelPointNetworksForm();
        }
        if (\Tools::isSubmit('submitTrackingEvents')) {
            $this->handleTrackingEventsForm();
        }
        $boxtalconnect = \boxtalconnect::getInstance();
        if (true === ShopUtil::$multistore && null === ShopUtil::$shopId) {
            $this->content = $boxtalconnect->displayTemplate('admin/multistoreAccessDenied.tpl');
            //phpcs:ignore
            return;
        } elseif (!AuthUtil::canUsePlugin()) {
            $this->content = $boxtalconnect->displayTemplate('admin/accessDenied.tpl');
            //phpcs:ignore
            return;
        }

        $smarty = $boxtalconnect->getSmarty();
        $parcelPointNetworks = unserialize(ConfigurationUtil::get('BX_PP_NETWORKS'));
        $smarty->assign('parcelPointNetworks', $parcelPointNetworks);
        $carriers = ShippingMethodUtil::getShippingMethods();
        foreach ((array) $carriers as $c => $carrier) {
            if (file_exists(_PS_SHIP_IMG_DIR_.(int) $carrier['id_carrier'].'.jpg')) {
                $carriers[$c]['logo'] = _THEME_SHIP_DIR_.(int) $carrier['id_carrier'].'.jpg';
            }
            $carriers[$c]['parcel_point_networks'] = unserialize($carriers[$c]['parcel_point_networks']);
        }
        $smarty->assign('carriers', $carriers);

        //phpcs:ignore
        $langId = $cookie->id_lang;
        $orderStatuses = OrderUtil::getOrderStatuses($langId);
        $smarty->assign('orderStatuses', $orderStatuses);
        $orderShipped = ConfigurationUtil::get('BX_ORDER_SHIPPED');
        $orderDelivered = ConfigurationUtil::get('BX_ORDER_DELIVERED');

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

        $this->content = $boxtalconnect->displayTemplate('admin/configuration/settings.tpl');
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
            $parcelPointNetworks = \Tools::isSubmit('parcelPointNetworks_'.(int) $carrier['id_carrier']) ? \Tools::getValue('parcelPointNetworks_'.(int) $carrier['id_carrier']) : array();
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

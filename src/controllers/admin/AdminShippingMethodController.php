<?php
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
        global $cookie;

        parent::init();
        if (\Tools::isSubmit('submitParcelPointNetworks')) {
            $this->handleParcelPointNetworksForm();
        }
        if (\Tools::isSubmit('submitTrackingEvents')) {
            $this->handleTrackingEventsForm();
        }
        $boxtalConnect = \boxtalconnect::getInstance();
        if (\Shop::isFeatureActive() && null === $boxtalConnect->shopGroupId && null === $boxtalConnect->shopId) {
            $this->content = $boxtalConnect->displayTemplate('admin/multistoreAccessDenied.tpl');
            //phpcs:ignore
            return;
        } elseif (!AuthUtil::canUsePlugin($boxtalConnect->shopGroupId, $boxtalConnect->shopId, $boxtalConnect->multistore)) {
            $this->content = $boxtalConnect->displayTemplate('admin/accessDenied.tpl');
            //phpcs:ignore
            return;
        }

        $smarty = $boxtalConnect->getSmarty();
        $parcelPointNetworks = unserialize(ConfigurationUtil::get('BX_PP_NETWORKS', $boxtalConnect->shopGroupId, $boxtalConnect->shopId));
        $smarty->assign('parcelPointNetworks', $parcelPointNetworks);
        $carriers = ShippingMethodUtil::getShippingMethods($boxtalConnect->shopGroupId, $boxtalConnect->shopId);
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
        $smarty->assign('orderShipped', ConfigurationUtil::get('BX_ORDER_SHIPPED', $boxtalConnect->shopGroupId, $boxtalConnect->shopId));
        $smarty->assign('orderDelivered', ConfigurationUtil::get('BX_ORDER_DELIVERED', $boxtalConnect->shopGroupId, $boxtalConnect->shopId));

        $this->content = $boxtalConnect->displayTemplate('admin/configuration/settings.tpl');
    }

    /**
     * Handle parcel point networks form.
     *
     * @void
     */
    private function handleParcelPointNetworksForm()
    {
        $boxtalconnect = boxtalconnect::getInstance();
        $carriers = ShippingMethodUtil::getShippingMethods($boxtalconnect->shopGroupId, $boxtalconnect->shopId);
        foreach ((array) $carriers as $carrier) {
            if (\Tools::isSubmit('parcelPointNetworks_'.(int) $carrier['id_carrier'])) {
                \Db::getInstance()->execute(
                    "INSERT INTO `"._DB_PREFIX_."bx_carrier` (`id_carrier`, `id_shop_group`, `id_shop`, `parcel_point_networks`)
                    VALUES ('".(int) $carrier['id_carrier']."', $boxtalconnect->shopGroupId, $boxtalconnect->shopId, '".pSQL(serialize(\Tools::getValue('parcelPointNetworks_'.(int) $carrier['id_carrier'])))."')
                    ON DUPLICATE KEY UPDATE parcel_point_networks='".pSQL(serialize(\Tools::getValue('parcelPointNetworks_'.(int) $carrier['id_carrier'])))."'"
                );
            } else {
                \Db::getInstance()->execute(
                    "INSERT INTO `"._DB_PREFIX_."bx_carrier` (`id_carrier`, `parcel_point_networks`)
                    VALUES ('".(int) $carrier['id_carrier']."', $boxtalconnect->shopGroupId, $boxtalconnect->shopId, '".pSQL(serialize(\Tools::getValue('parcelPointNetworks_'.(int) $carrier['id_carrier'])))."')
                    ON DUPLICATE KEY UPDATE parcel_point_networks='".pSQL(serialize(array()))."'"
                );
            }
        }
    }

    /**
     * Handle tracking events form.
     *
     * @void
     */
    private function handleTrackingEventsForm()
    {
        $boxtalConnect = boxtalConnect::getInstance();
        if (\Tools::isSubmit('orderShipped')) {
            $status = \Tools::getValue('orderShipped');
            if ('' === $status) {
                ConfigurationUtil::set('BX_ORDER_SHIPPED', null, $boxtalConnect->shopGroupId, $boxtalConnect->shopId);
            } else {
                ConfigurationUtil::set('BX_ORDER_SHIPPED', $status, $boxtalConnect->shopGroupId, $boxtalConnect->shopId);
            }
        }

        if (\Tools::isSubmit('orderDelivered')) {
            $status = \Tools::getValue('orderDelivered');
            if ('' === $status) {
                ConfigurationUtil::set('BX_ORDER_DELIVERED', null, $boxtalConnect->shopGroupId, $boxtalConnect->shopId);
            } else {
                ConfigurationUtil::set('BX_ORDER_DELIVERED', $status, $boxtalConnect->shopGroupId, $boxtalConnect->shopId);
            }
        }
    }
}

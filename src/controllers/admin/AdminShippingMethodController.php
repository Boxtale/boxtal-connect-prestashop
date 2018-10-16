<?php
/**
 * Contains code for the shipping method admin controller.
 */

use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShippingMethodUtil;

/**
 * Ajax admin controller class.
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
        $this->handleForm();
        $boxtalConnect = BoxtalConnect::getInstance();
        if (!AuthUtil::canUsePlugin()) {
            $this->content = $boxtalConnect->displayTemplate('admin/accessDenied.tpl');
            return;
        }

        $smarty = $boxtalConnect->getSmarty();
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
        $this->content = $boxtalConnect->displayTemplate('admin/shipping-method/settings.tpl');
    }

    /**
     * Handles parcel point operators form.
     *
     * @void
     */
    private function handleForm()
    {
        if (\Tools::isSubmit('submitParcelPointNetworks')) {
            $carriers = ShippingMethodUtil::getShippingMethods();
            foreach ((array) $carriers as $carrier) {
                if (\Tools::isSubmit('parcelPointNetworks_'.(int) $carrier['id_carrier'])) {
                    \Db::getInstance()->execute(
                        "INSERT INTO `"._DB_PREFIX_."bx_carrier` (`id_carrier`, `parcel_point_networks`)
                        VALUES ('".(int) $carrier['id_carrier']."', '".pSQL(serialize(\Tools::getValue('parcelPointNetworks_'.(int) $carrier['id_carrier'])))."')
                        ON DUPLICATE KEY UPDATE parcel_point_networks='".pSQL(serialize(\Tools::getValue('parcelPointNetworks_'.(int) $carrier['id_carrier'])))."'"
                    );
                } else {
                    \Db::getInstance()->execute(
                        "INSERT INTO `"._DB_PREFIX_."bx_carrier` (`id_carrier`, `parcel_point_networks`)
                        VALUES ('".(int) $carrier['id_carrier']."', '".pSQL(serialize(\Tools::getValue('parcelPointNetworks_'.(int) $carrier['id_carrier'])))."')
                        ON DUPLICATE KEY UPDATE parcel_point_networks='".pSQL(serialize(array()))."'"
                    );
                }
            }
        }
    }
}

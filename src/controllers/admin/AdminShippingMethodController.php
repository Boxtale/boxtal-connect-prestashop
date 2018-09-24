<?php
/**
 * Contains code for the shipping method admin controller.
 */

use Boxtal\BoxtalPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalPrestashop\Util\ShippingMethodUtil;


/**
 * Ajax admin controller class.
 */
class AdminShippingMethodController extends \ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'AdminShippingMethodController';
        parent::__construct();
    }

    public function init()
    {
        parent::init();
        $this->handleForm();
        $boxtal = Boxtal::getInstance();
        if (!AuthUtil::canUsePlugin()) {
            $this->content = $boxtal->displayTemplate('admin/accessDenied.tpl');
            return;
        }

        $smarty = $boxtal->getSmarty();
        $parcelPointOperators = unserialize(ConfigurationUtil::get('BX_PP_OPERATORS'));
        $smarty->assign('parcelPointOperators', $parcelPointOperators);
        $carriers = ShippingMethodUtil::getShippingMethods();
        foreach ((array)$carriers as $c => $carrier) {
            if (file_exists(_PS_SHIP_IMG_DIR_.(int)$carrier['id_carrier'].'.jpg')) {
                $carriers[$c]['logo'] = _THEME_SHIP_DIR_.(int)$carrier['id_carrier'].'.jpg';
            }
            $carriers[$c]['parcel_point_operators'] = unserialize($carriers[$c]['parcel_point_operators']);
        }
        $smarty->assign('carriers', $carriers);
        $this->content = $boxtal->displayTemplate('admin/shipping-method/settings.tpl');
    }

    public function handleForm()
    {
        if (\Tools::isSubmit('submitParcelPointOperators')) {
            $carriers = ShippingMethodUtil::getShippingMethods();
            foreach ((array)$carriers as $carrier) {
                if (\Tools::isSubmit('parcelPointOperators_'.(int)$carrier['id_carrier'])) {
                    \Db::getInstance()->execute(
                        "INSERT INTO `"._DB_PREFIX_."bx_carrier` (`id_carrier`, `parcel_point_operators`)
                        VALUES ('".(int)$carrier['id_carrier']."', '".pSQL(serialize(\Tools::getValue('parcelPointOperators_'.(int)$carrier['id_carrier'])))."')
                        ON DUPLICATE KEY UPDATE parcel_point_operators='".pSQL(serialize(\Tools::getValue('parcelPointOperators_'.(int)$carrier['id_carrier'])))."'"
                    );
                } else {
                    \Db::getInstance()->execute(
                        "INSERT INTO `"._DB_PREFIX_."bx_carrier` (`id_carrier`, `parcel_point_operators`)
                        VALUES ('".(int)$carrier['id_carrier']."', '".pSQL(serialize(\Tools::getValue('parcelPointOperators_'.(int)$carrier['id_carrier'])))."')
                        ON DUPLICATE KEY UPDATE parcel_point_operators='".pSQL(serialize(array()))."'"
                    );
                }
            }
        }
    }
}

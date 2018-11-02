<?php
/**
 * Contains code for the setup wizard class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Init;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;

/**
 * Setup wizard class.
 *
 * Display setup wizard if needed.
 *
 * @class       SetupWizard
 *
 */
class SetupWizard
{

    /**
     * Construct function.
     *
     * @param \boxtalconnect $plugin plugin instance.
     *
     * @void
     */
    public function __construct($plugin)
    {
        $shops = ShopUtil::getShops();
        var_dump($shops);
        foreach ($shops as $shop) {
            if (AuthUtil::isPluginPaired($shop['id_shop_group'], $shop['id_shop'])) {
                if (NoticeController::hasNotice(NoticeController::$setupWizard, $shop['id_shop_group'], $shop['id_shop'])) {
                    NoticeController::removeNotice(NoticeController::$setupWizard, $shop['id_shop_group'], $shop['id_shop']);
                }
                if (ConfigurationUtil::hasConfiguration($shop['id_shop_group'], $shop['id_shop']) && NoticeController::hasNotice(NoticeController::$configurationFailure, $shop['id_shop_group'], $shop['id_shop'])) {
                    NoticeController::removeNotice(NoticeController::$configurationFailure, $shop['id_shop_group'], $shop['id_shop']);
                } elseif (! ConfigurationUtil::hasConfiguration($shop['id_shop_group'], $shop['id_shop']) && ! NoticeController::hasNotice(NoticeController::$configurationFailure, $shop['id_shop_group'], $shop['id_shop'])) {
                    NoticeController::addNotice(NoticeController::$configurationFailure, $shop['id_shop_group'], $shop['id_shop']);
                }
            } elseif (! AuthUtil::isPluginPaired($shop['id_shop_group'], $shop['id_shop']) && ! NoticeController::hasNotice(NoticeController::$setupWizard, $shop['id_shop_group'], $shop['id_shop'])) {
                NoticeController::addNotice(NoticeController::$setupWizard, $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }
}

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
        if (1 === $plugin->multistore) {
            $shops = ShopUtil::getShops();
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
        } else {
            if (AuthUtil::isPluginPaired($plugin->shopGroupId, $plugin->shopId)) {
                if (NoticeController::hasNotice(NoticeController::$setupWizard, $plugin->shopGroupId, $plugin->shopId)) {
                    NoticeController::removeNotice(NoticeController::$setupWizard, $plugin->shopGroupId, $plugin->shopId);
                }
                if (ConfigurationUtil::hasConfiguration($plugin->shopGroupId, $plugin->shopId) && NoticeController::hasNotice(NoticeController::$configurationFailure, $plugin->shopGroupId, $plugin->shopId)) {
                    NoticeController::removeNotice(NoticeController::$configurationFailure, $plugin->shopGroupId, $plugin->shopId);
                } elseif (! ConfigurationUtil::hasConfiguration($plugin->shopGroupId, $plugin->shopId) && ! NoticeController::hasNotice(NoticeController::$configurationFailure, $plugin->shopGroupId, $plugin->shopId)) {
                    NoticeController::addNotice(NoticeController::$configurationFailure, $plugin->shopGroupId, $plugin->shopId);
                }
            } elseif (! AuthUtil::isPluginPaired($plugin->shopGroupId, $plugin->shopId) && ! NoticeController::hasNotice(NoticeController::$setupWizard, $plugin->shopGroupId, $plugin->shopId)) {
                NoticeController::addNotice(NoticeController::$setupWizard, $plugin->shopGroupId, $plugin->shopId);
            }
        }
    }
}

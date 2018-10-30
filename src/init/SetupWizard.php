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
        $shopContext = ShopUtil::getShopContext();

        if (null === $shopContext['id_shop'] && null === $shopContext['id_shop_group']) {
            $shops = ShopUtil::getShops();
            foreach ($shops as $shop) {
                $shopId = $shop['id_shop'];
                $shopGroupId = $shop['id_shop_group'];
                if (AuthUtil::isPluginPaired($shopGroupId, $shopId)) {
                    if (NoticeController::hasNotice(NoticeController::$setupWizard, $shopGroupId, $shopId)) {
                        NoticeController::removeNotice(NoticeController::$setupWizard, $shopGroupId, $shopId);
                    }
                    if (ConfigurationUtil::hasConfiguration($shopGroupId, $shopId) && NoticeController::hasNotice(NoticeController::$configurationFailure, $shopGroupId, $shopId)) {
                        NoticeController::removeNotice(NoticeController::$configurationFailure, $shopGroupId, $shopId);
                    } elseif (! ConfigurationUtil::hasConfiguration($shopGroupId, $shopId) && ! NoticeController::hasNotice(NoticeController::$configurationFailure, $shopGroupId, $shopId)) {
                        NoticeController::addNotice(NoticeController::$configurationFailure, $shopGroupId, $shopId);
                    }
                } elseif (! AuthUtil::isPluginPaired($shopGroupId, $shopId) && ! NoticeController::hasNotice(NoticeController::$setupWizard, $shopGroupId, $shopId)) {
                    NoticeController::addNotice(NoticeController::$setupWizard, $shopGroupId, $shopId);
                }
            }
        } else {
            if (AuthUtil::isPluginPaired($shopContext['id_shop_group'], $shopContext['id_shop'])) {
                if (NoticeController::hasNotice(NoticeController::$setupWizard, $shopContext['id_shop_group'], $shopContext['id_shop'])) {
                    NoticeController::removeNotice(NoticeController::$setupWizard, $shopContext['id_shop_group'], $shopContext['id_shop']);
                }
                if (ConfigurationUtil::hasConfiguration($shopContext['id_shop_group'], $shopContext['id_shop']) && NoticeController::hasNotice(NoticeController::$configurationFailure, $shopContext['id_shop_group'], $shopContext['id_shop'])) {
                    NoticeController::removeNotice(NoticeController::$configurationFailure, $shopContext['id_shop_group'], $shopContext['id_shop']);
                } elseif (! ConfigurationUtil::hasConfiguration($shopContext['id_shop_group'], $shopContext['id_shop']) && ! NoticeController::hasNotice(NoticeController::$configurationFailure, $shopContext['id_shop_group'], $shopContext['id_shop'])) {
                    NoticeController::addNotice(NoticeController::$configurationFailure, $shopContext['id_shop_group'], $shopContext['id_shop']);
                }
            } elseif (! AuthUtil::isPluginPaired($shopContext['id_shop_group'], $shopContext['id_shop']) && ! NoticeController::hasNotice(NoticeController::$setupWizard, $shopContext['id_shop_group'], $shopContext['id_shop'])) {
                NoticeController::addNotice(NoticeController::$setupWizard, $shopContext['id_shop_group'], $shopContext['id_shop']);
            }
        }
    }
}

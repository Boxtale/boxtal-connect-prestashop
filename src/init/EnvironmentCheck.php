<?php
/**
 * Contains code for the environment check class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Init;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\EnvironmentUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;

/**
 * Environment check class.
 *
 * Display environment warning if needed.
 *
 * @class       EnvironmentCheck
 *
 */
class EnvironmentCheck
{

    /**
     * Construct function.
     *
     * @param \boxtalconnect $plugin plugin array.
     *
     * @void
     */
    public function __construct($plugin)
    {
        $environmentWarning = EnvironmentUtil::checkErrors($plugin);

        if (false !== $environmentWarning) {
            NoticeController::removeAllNotices();
            NoticeController::addNotice(
                NoticeController::$environmentWarning,
                ShopUtil::$shopGroupId,
                ShopUtil::$shopId,
                array(
                    'message' => $environmentWarning,
                )
            );
        } elseif (NoticeController::hasNotice(NoticeController::$environmentWarning, ShopUtil::$shopGroupId, ShopUtil::$shopId)) {
            NoticeController::removeNotice(NoticeController::$environmentWarning, ShopUtil::$shopGroupId, ShopUtil::$shopId);
        }
    }
}

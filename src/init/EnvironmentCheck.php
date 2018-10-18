<?php
/**
 * Contains code for the environment check class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Init;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\EnvironmentUtil;

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
                array(
                    'message' => $environmentWarning,
                )
            );
        } elseif (NoticeController::hasNotice(NoticeController::$environmentWarning)) {
            NoticeController::removeNotice(NoticeController::$environmentWarning);
        }
    }
}

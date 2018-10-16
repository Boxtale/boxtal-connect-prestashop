<?php
/**
 * Contains code for the setup wizard class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Init;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\AuthUtil;
use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;

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
     * @param \boxtalconnect plugin instance.
     *
     * @void
     */
    public function __construct($plugin)
    {
        if (AuthUtil::isPluginPaired()) {
            if (NoticeController::hasNotice(NoticeController::$setupWizard)) {
                NoticeController::removeNotice(NoticeController::$setupWizard);
            }
            if (ConfigurationUtil::hasConfiguration() && NoticeController::hasNotice(NoticeController::$configurationFailure)) {
                NoticeController::removeNotice(NoticeController::$configurationFailure);
            } elseif (! ConfigurationUtil::hasConfiguration() && ! NoticeController::hasNotice(NoticeController::$configurationFailure)) {
                NoticeController::addNotice(NoticeController::$configurationFailure);
            }
        } elseif (! AuthUtil::isPluginPaired() && ! NoticeController::hasNotice(NoticeController::$setupWizard)) {
            NoticeController::addNotice(NoticeController::$setupWizard);
        }
    }
}

<?php
/**
 * Contains code for the setup wizard class.
 */

namespace Boxtal\BoxtalPrestashop\Init;

use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalPrestashop\Util\AuthUtil;
use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;

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
     * @void
     */
    public function __construct()
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

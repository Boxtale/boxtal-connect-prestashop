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
    public function __construct() {
        if ( AuthUtil::isPluginPaired() && NoticeController::hasNotice( NoticeController::$setupWizard ) ) {
            NoticeController::removeNotice( NoticeController::$setupWizard );
        } elseif ( ! AuthUtil::isPluginPaired() && ! NoticeController::hasNotice( NoticeController::$setupWizard ) ) {
            if ( ConfigurationUtil::getConfiguration() ) {
                NoticeController::addNotice( NoticeController::$setupWizard );
                if ( NoticeController::hasNotice( NoticeController::$setupFailure ) ) {
                    NoticeController::removeNotice( NoticeController::$setupFailure );
                }
            } else {
                NoticeController::addNotice( NoticeController::$setupFailure );
            }
        }
    }
}

<?php
/**
 * Contains code for the setup wizard notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

use Boxtal\BoxtalConnectPrestashop\Util\ConfigurationUtil;

/**
 * Setup wizard notice class.
 *
 * Setup wizard notice used to display setup wizard.
 *
 * @class       SetupWizardNotice
 *
 */
class SetupWizardNotice extends AbstractNotice
{

    /**
     * Onboarding link.
     *
     * @var string $onboarding_link url.
     */
    public $onboardingLink;

    /**
     * Construct function.
     *
     * @param string $key         key for notice.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     *
     * @void
     */
    public function __construct($key, $shopGroupId, $shopId)
    {
        parent::__construct($key, $shopGroupId, $shopId);
        $this->type         = 'setupWizard';
        $this->autodestruct = false;
        $this->onboardingLink = ConfigurationUtil::getOnboardingLink($shopGroupId, $shopId);
        $this->template = 'setupWizard';
    }
}

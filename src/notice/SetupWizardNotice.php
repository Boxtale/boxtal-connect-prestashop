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
     * @param string $key key for notice.
     *
     * @void
     */
    public function __construct($key)
    {
        parent::__construct($key);
        $this->type         = 'setupWizard';
        $this->autodestruct = false;
        $this->onboardingLink = ConfigurationUtil::getOnboardingLink();
        $this->template = 'setupWizard';
    }
}

<?php
/**
 * Contains code for the setup wizard notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

use Boxtal\BoxtalPrestashop\Util\ConfigurationUtil;

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
     * Connect link.
     *
     * @var string $connectLink url.
     */
    public $signupLink;

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
        $this->signupLink = $this->getSignupUrl();
        $this->template = 'setupWizard';
    }

    /**
     * Build signup link.
     *
     * @return string signup link
     */
    public function getSignupUrl()
    {
        $signupLink = ConfigurationUtil::get('BX_SIGNUP_URL');
        $sql = new \DbQuery();
        $sql->select('e.email');
        $sql->from('employee', 'e');
        $sql->where('e.id_profile = 1');
        $sql->orderBy('e.id_employee asc');
        $sql->limit('limit(0,1)');
        $adminUser = \Db::getInstance()->executeS($sql)[0];

        $params       = array(
            'email'       => $adminUser['email'],
            'shopUrl'     => \Tools::getHttpHost(true).__PS_BASE_URI__,
            'shopType' => 'prestashop'
        );
        return $signupLink.'?'.http_build_query($params);
    }
}

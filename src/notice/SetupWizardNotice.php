<?php
/**
 * Contains code for the setup wizard notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

/**
 * Setup wizard notice class.
 *
 * Setup wizard notice used to display setup wizard.
 *
 * @class       SetupWizardNotice
 * @package     Boxtal\BoxtalPrestashop\Notice
 */
class SetupWizardNotice extends AbstractNotice {

    /**
     * Base connect link.
     *
     * @var string $baseConnectLink url.
     */
    public $baseConnectLink;

    /**
     * Connect link.
     *
     * @var string $connectLink url.
     */
    public $connectLink;

    /**
     * Return url.
     *
     * @var string $returnUrl.
     */
    public $returnUrl;

    /**
     * Construct function.
     *
     * @param string $key key for notice.
     * @void
     */
    public function __construct( $key ) {
        parent::__construct( $key );
        $this->type         = 'setupWizard';
        $this->autodestruct = false;
        $this->setBaseConnectLink( 'http://localhost:4200/app/connect-shop' );
        $this->returnUrl   = $this->getDashboardUrl();
        $this->connectLink = $this->getConnectUrl();
        $this->template = 'setupWizard';
    }

    /**
     * Build connect link.
     *
     * @return string connect link
     */
    public function getConnectUrl() {
        $connectUrl = $this->baseConnectLink;
        $sql = new \DbQuery();
        $sql->select('e.firstname, e.lastname, e.email');
        $sql->from('employee', 'e');
        $sql->where('e.id_profile = 1');
        $sql->orderBy('e.id_employee asc');
        $sql->limit('limit(0,1)');
        $adminUser = \Db::getInstance()->executeS($sql)[0];

        $boxtal = \Boxtal::getInstance();
        $isoCode = \Language::getIsoById( (int)$boxtal->getContext()->cookie->id_lang );

        $params       = array(
            'firstName'   => $adminUser['firstname'],
            'lastName'    => $adminUser['lastname'],
            'email'       => $adminUser['email'],
            'shopUrl'     => \Tools::getHttpHost(true).__PS_BASE_URI__,
            'returnUrl'   => $this->returnUrl,
            'connectType' => 'prestashop',
            'locale'      => $isoCode,
        );
        $connectUrl .= '?' . http_build_query( $params );
        return $connectUrl;
    }

    /**
     * Get dashboard link.
     *
     * @return string dashboard url
     */
    public function getDashboardUrl()
    {
        $boxtal = \Boxtal::getInstance();
        return $boxtal->getContext()->link->getAdminLink('AdminDashboard');
    }

    /**
     * Build connect link.
     *
     * @param string $url base connect link.
     * @void
     */
    public function setBaseConnectLink( $url ) {
        $this->baseConnectLink = $url;
    }

    /**
     * Set return url.
     *
     * @param string $url new return url.
     * @void
     */
    public function setReturnUrl( $url ) {
        $this->returnUrl = $url;
    }
}

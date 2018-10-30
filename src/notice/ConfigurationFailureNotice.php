<?php
/**
 * Contains code for the configuration failure notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

/**
 * Configuration failure notice class.
 *
 * Configuration failure notice used to display setup error.
 *
 * @class       ConfigurationFailureNotice
 *
 */
class ConfigurationFailureNotice extends AbstractNotice
{

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
        $this->type         = 'configurationFailure';
        $this->autodestruct = false;
        $this->template = 'configurationFailure';
    }
}

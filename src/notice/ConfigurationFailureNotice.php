<?php
/**
 * Contains code for the configuration failure notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

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
     * @param string $key key for notice.
     *
     * @void
     */
    public function __construct($key)
    {
        parent::__construct($key);
        $this->type         = 'configurationFailure';
        $this->autodestruct = false;
        $this->template = 'configurationFailure';
    }
}

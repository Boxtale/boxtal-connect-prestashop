<?php
/**
 * Contains code for the setup failure notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

/**
 * Setup failure notice class.
 *
 * Setup failure notice used to display setup error.
 *
 * @class       SetupFailureNotice
 *
 */
class SetupFailureNotice extends AbstractNotice
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
        $this->type         = 'setupFailure';
        $this->autodestruct = false;
        $this->template = 'setupFailure';
    }
}

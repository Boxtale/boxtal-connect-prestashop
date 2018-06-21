<?php
/**
 * Contains code for the pairing update notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

/**
 * Pairing update notice class.
 *
 * Enables pairing update validation.
 *
 * @class       PairingUpdateNotice
 *
 */
class PairingUpdateNotice extends AbstractNotice
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
        $this->type         = 'pairing-update';
        $this->autodestruct = false;
        $this->template = 'pairingUpdate';
    }
}

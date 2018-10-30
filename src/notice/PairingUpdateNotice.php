<?php
/**
 * Contains code for the pairing update notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

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
     * @param string $key         key for notice.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     *
     * @void
     */
    public function __construct($key, $shopGroupId, $shopId)
    {
        parent::__construct($key, $shopGroupId, $shopId);
        $this->type         = 'pairing-update';
        $this->autodestruct = false;
        $this->template = 'pairingUpdate';
    }
}

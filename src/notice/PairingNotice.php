<?php
/**
 * Contains code for the pairing notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

/**
 * Pairing notice class.
 *
 * Successful pairing notice.
 *
 * @class       PairingNotice
 *
 */
class PairingNotice extends AbstractNotice
{

    /**
     * Whether pairing was a success or not.
     *
     * @var bool
     */
    protected $result;

    /**
     * Construct function.
     *
     * @param string $key         key for notice.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     * @param array  $args        additional args.
     *
     * @void
     */
    public function __construct($key, $shopGroupId, $shopId, $args)
    {
        parent::__construct($key, $shopGroupId, $shopId);
        $this->type         = 'pairing';
        $this->autodestruct = false;
        $this->template = $args['result'] ? 'pairingSuccess' : 'pairingFailure';
    }
}

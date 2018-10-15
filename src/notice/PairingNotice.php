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
     * @param string $key  key for notice.
     * @param array  $args additional args.
     *
     * @void
     */
    public function __construct($key, $args)
    {
        parent::__construct($key);
        $this->type         = 'pairing';
        $this->autodestruct = false;
        $this->template = $args['result'] ? 'pairingSuccess' : 'pairingFailure';
    }
}

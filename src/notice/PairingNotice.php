<?php
/**
 * Contains code for the pairing notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

/**
 * Pairing notice class.
 *
 * Successful pairing notice.
 *
 * @class       PairingNotice
 * @package     Boxtal\BoxtalPrestashop\Notice
 */
class PairingNotice extends AbstractNotice {

    /**
     * Whether pairing was a success or not.
     *
     * @var boolean
     */
    protected $result;

    /**
     * Construct function.
     *
     * @param string $key key for notice.
     * @param array  $args additional args.
     * @void
     */
    public function __construct( $key, $args ) {
        parent::__construct( $key );
        $this->type         = 'pairing';
        $this->autodestruct = false;
        $this->template = $args['result'] ? 'pairingSuccess' : 'pairingFailure';
    }
}

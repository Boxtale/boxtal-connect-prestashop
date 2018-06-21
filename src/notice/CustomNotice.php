<?php
/**
 * Contains code for the custom notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

/**
 * Custom notice class.
 *
 * Custom notice where message and status determine display.
 *
 * @class       CustomNotice
 *
 */
class CustomNotice extends AbstractNotice
{

    /**
     * Notice message.
     *
     * @var string
     */
    protected $message;

    /**
     * Notice status.
     *
     * @var string (accepted statuses: 'warning', 'info', 'success')
     */
    protected $status;

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
        $this->type         = 'custom';
        $this->autodestruct = isset($args['autodestruct']) ? $args['autodestruct'] : true;
        $this->status       = isset($args['status']) ? $args['status'] : 'info';
        $this->message      = isset($args['message']) ? $args['message'] : '';
        $this->template = 'custom';
    }
}

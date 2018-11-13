<?php
/**
 * Contains code for the environment warning notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

/**
 * Environment warning notice class.
 *
 * Environment warning notice.
 *
 * @class       EnvironmentWarningNotice
 *
 */
class EnvironmentWarningNotice extends AbstractNotice
{

    /**
     * Message.
     *
     * @var string $message message.
     */
    public $message;

    /**
     * Construct function.
     *
     * @param string $key         key for notice.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     * @param array  $args        additional arguments.
     *
     * @void
     */
    public function __construct($key, $shopGroupId, $shopId, $args)
    {
        parent::__construct($key, $shopGroupId, $shopId);
        $this->type         = 'environmentWarning';
        $this->autodestruct = false;
        $this->message = isset($args['message']) ? $args['message'] : '';
        $this->template = 'environmentWarning';
    }
}

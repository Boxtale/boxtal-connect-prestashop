<?php
/**
 * Contains code for the abstract notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\Notice;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;

/**
 * Abstract notice class.
 *
 * Base methods for notices.
 *
 * @class       AbstractNotice
 *
 */
abstract class AbstractNotice
{

    /**
     * Boxtal module instance.
     *
     * @var \Boxtal
     */
    protected $boxtal;

    /**
     * Notice key, used for remove method.
     *
     * @var string
     */
    protected $key;

    /**
     * Notice type.
     *
     * @var string
     */
    public $type;

    /**
     * Notice template.
     *
     * @var string
     */
    public $template;

    /**
     * Notice autodestruct.
     *
     * @var bool
     */
    protected $autodestruct;

    /**
     * Construct function.
     *
     * @param string $key key for notice.
     *
     * @void
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Render notice.
     *
     * @void
     */
    public function render()
    {
        $notice = $this;
        if ($notice->isValid()) {
            $boxtal = \BoxtalConnect::getInstance();
            $ajaxLink = \Context::getContext()->link->getAdminLink('AdminAjax');
            include realpath(dirname(__DIR__)).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'notice'.DIRECTORY_SEPARATOR.'wrapper.php';
            if ($notice->autodestruct) {
                $notice->remove();
            }
        } else {
            $notice->remove();
        }
    }

    /**
     * Remove notice.
     *
     * @void
     */
    public function remove()
    {
        NoticeController::removeNotice($this->key);
    }

    /**
     * Check if notice is still valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        return true;
    }
}

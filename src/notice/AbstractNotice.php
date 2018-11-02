<?php
/**
 * Contains code for the abstract notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\Notice;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;

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
     * boxtalconnect instance.
     *
     * @var \boxtalconnect
     */
    protected $boxtalConnect;

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
     * Notice shop group id.
     *
     * @var int
     */
    protected $shopGroupId;

    /**
     * Notice shop id.
     *
     * @var int
     */
    protected $shopId;

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
        $this->key = $key;
        $this->shopGroupId = $shopGroupId;
        $this->shopId = $shopId;
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
            $boxtalconnect = \boxtalconnect::getInstance();
            $ajaxLink = \Context::getContext()->link->getAdminLink('AdminAjax');
            //phpcs:ignore
            $shopName = ShopUtil::getShopName($notice->shopGroupId, $notice->shopId);
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
        NoticeController::removeNotice($this->key, $this->shopGroupId, $this->shopId);
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

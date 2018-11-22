<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Boxtal <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for the abstract notice class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Notice;

use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\Notice;
use Boxtal\BoxtalConnectPrestashop\Controllers\Misc\NoticeController;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;
use BoxtalConnect;

/**
 * Abstract notice class.
 *
 * Base methods for notices.
 *
 * @class       AbstractNotice
 */
abstract class AbstractNotice
{
    /**
     * BoxtalConnect instance.
     *
     * @var \BoxtalConnect
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
     * @param string $key key for notice
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
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
            $boxtalConnect = BoxtalConnect::getInstance();
            $ajaxLink = $boxtalConnect->getContext()->link->getAdminLink('AdminAjax');
            //phpcs:ignore
            $shopName = ShopUtil::getShopName($notice->shopGroupId, $notice->shopId);
            include realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR .
                'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'notice' . DIRECTORY_SEPARATOR .
                'wrapper.php';
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
     * @return bool
     */
    public function isValid()
    {
        return true;
    }
}

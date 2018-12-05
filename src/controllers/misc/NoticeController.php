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
 * Contains code for the notice controller class.
 */

namespace Boxtal\BoxtalConnectPrestashop\Controllers\Misc;

use Boxtal\BoxtalConnectPrestashop\Notice\CustomNotice;
use Boxtal\BoxtalConnectPrestashop\Util\ShopUtil;

/**
 * Notice controller class.
 *
 * parcelPoint for notices.
 *
 * @class       NoticeController
 */
class NoticeController
{
    /**
     * Notice name.
     *
     * @var string
     */
    public static $update = 'update';

    /**
     * Notice name.
     *
     * @var string
     */
    public static $setupWizard = 'setupWizard';

    /**
     * Notice name.
     *
     * @var string
     */
    public static $configurationFailure = 'configurationFailure';

    /**
     * Notice name.
     *
     * @var string
     */
    public static $pairing = 'pairing';

    /**
     * Notice name.
     *
     * @var string
     */
    public static $pairingUpdate = 'pairingUpdate';

    /**
     * Notice name.
     *
     * @var string
     */
    public static $custom = 'custom';

    /**
     * Notice name.
     *
     * @var string
     */
    public static $environmentWarning = 'environmentWarning';

    /**
     * Array of notices - name => callback.
     *
     * @var array
     */
    private static $coreNotices = array(
        'update',
        'setupWizard',
        'pairing',
        'pairingUpdate',
        'configurationFailure',
        'environmentWarning',
    );

    /**
     * Get notice instances.
     *
     * @return array $notices instances of notices
     */
    public static function getNoticeInstances()
    {
        $notices = self::getNoticeKeys();
        $noticeInstances = array();
        if (is_array($notices)) {
            foreach ($notices as $notice) {
                $key = $notice['key'];
                $classname = 'Boxtal\BoxtalConnectPrestashop\Notice\\';
                if (!in_array($key, self::$coreNotices, true)) {
                    $value = unserialize($notice['value']);
                    if (false !== $value) {
                        $class = new CustomNotice($key, $notice['id_shop_group'], $notice['id_shop'], $value);
                        $noticeInstances[] = $class;
                    } else {
                        self::removeNotice($key, $notice['id_shop_group'], $notice['id_shop']);
                    }
                } else {
                    $classname .= ucwords(str_replace('-', '', $key)) . 'Notice';
                    if (class_exists($classname, true)) {
                        $value = unserialize($notice['value']);
                        if (false !== $value && null !== $value) {
                            $class = new $classname($key, $notice['id_shop_group'], $notice['id_shop'], $value);
                        } else {
                            $class = new $classname($key, $notice['id_shop_group'], $notice['id_shop']);
                        }
                        $noticeInstances[] = $class;
                    }
                }
            }
        }

        return $noticeInstances;
    }

    /**
     * Get notice keys.
     *
     * @return array of notice keys
     */
    public static function getNoticeKeys()
    {
        $sql = new \DbQuery();
        $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
        $sql->from('bx_notices', 'n');
        if (ShopUtil::$multistore && null !== ShopUtil::$shopGroupId) {
            $sql->where('n.id_shop_group=' . (int) ShopUtil::$shopGroupId);
        }
        if (ShopUtil::$multistore && null !== ShopUtil::$shopId) {
            $sql->where('n.id_shop=' . (int) ShopUtil::$shopId);
        }

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Add notice.
     *
     * @param string $type type of notice
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     * @param mixed $args additional args
     *
     * @void
     */
    public static function addNotice($type, $shopGroupId, $shopId, $args = array())
    {
        if (!in_array($type, self::$coreNotices, true)) {
            $key = uniqid('bx_', false);
        } else {
            $key = $type;
        }

        $value = serialize($args);

        $sql = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'bx_notices` (`id_shop_group`, `id_shop`, `key`, `value`)
            VALUES (';

        if (null === $shopGroupId) {
            $sql .= 'null, ';
        } else {
            $sql .= $shopGroupId . ', ';
        }

        if (null === $shopId) {
            $sql .= 'null, ';
        } else {
            $sql .= $shopId . ', ';
        }

        $sql .= "'" . pSQL($key) . "', '" . pSQL($value) . "')";

        \Db::getInstance()->execute($sql);
    }

    /**
     * Remove notice.
     *
     * @param string $key notice key
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     *
     * @void
     */
    public static function removeNotice($key, $shopGroupId, $shopId)
    {
        $sql = 'DELETE IGNORE FROM `' . _DB_PREFIX_ . 'bx_notices` 
                WHERE ';
        if (null === $shopGroupId) {
            $sql .= '`id_shop_group` IS NULL ';
        } else {
            $sql .= '`id_shop_group`=' . $shopGroupId . ' ';
        }
        if (null === $shopId) {
            $sql .= 'AND `id_shop` IS NULL ';
        } else {
            $sql .= 'AND `id_shop`=' . $shopId . ' ';
        }
        $sql .= 'AND `key`="' . $key . '";';

        \Db::getInstance()->execute($sql);
    }

    /**
     * Whether there are active notices.
     *
     * @return bool
     */
    public static function hasNotices()
    {
        $notices = self::getNoticeKeys();

        return !empty($notices);
    }

    /**
     * Whether given notice is active.
     *
     * @param string $noticeKey notice key
     * @param int $shopGroupId shop group id
     * @param int $shopId shop id
     *
     * @return bool
     */
    public static function hasNotice($noticeKey, $shopGroupId, $shopId)
    {
        $sql = new \DbQuery();
        $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
        $sql->from('bx_notices', 'n');
        if (null === $shopGroupId) {
            $sql->where('n.id_shop_group IS NULL');
        } else {
            $sql->where('n.id_shop_group=' . (int) $shopGroupId);
        }

        if (null === $shopId) {
            $sql->where('n.id_shop IS NULL');
        } else {
            $sql->where('n.id_shop=' . (int) $shopId);
        }
        $sql->where('n.key="' . pSQL($noticeKey) . '"');
        $result = \Db::getInstance()->executeS($sql);

        return !empty($result);
    }

    /**
     * Remove all notices.
     *
     * @void
     */
    public static function removeAllNotices()
    {
        \DB::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'bx_notices`;'
        );
    }

    /**
     * Remove all notices.
     *
     * @void
     */
    public static function removeAllNoticesForShop()
    {
        \DB::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'bx_notices`
            WHERE `id_shop_group`="' . ShopUtil::$shopGroupId . '" AND `id_shop`="' . ShopUtil::$shopId . '";'
        );
    }
}

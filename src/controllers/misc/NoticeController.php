<?php
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
 *
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
    private static $coreNotices = array( 'update', 'setupWizard', 'pairing', 'pairingUpdate', 'configurationFailure', 'environmentWarning' );

    /**
     * Get notice instances.
     *
     * @return array $notices instances of notices.
     */
    public static function getNoticeInstances()
    {
        $notices = self::getNoticeKeys();
        $noticeInstances = array();
        if (is_array($notices)) {
            foreach ($notices as $notice) {
                $key = $notice['key'];
                $classname = 'Boxtal\BoxtalConnectPrestashop\Notice\\';
                if (! in_array($key, self::$coreNotices, true)) {
                    $value = unserialize($notice['value']);
                    if (false !== $value) {
                        $class             = new CustomNotice($key, $notice['id_shop_group'], $notice['id_shop'], $value);
                        $noticeInstances[] = $class;
                    } else {
                        self::removeNotice($key, $notice['id_shop_group'], $notice['id_shop']);
                    }
                } else {
                    $classname .= ucwords(str_replace('-', '', $key)).'Notice';
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
     * @return array of notice keys.
     */
    public static function getNoticeKeys()
    {
        $sql = new \DbQuery();
        $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
        $sql->from('bx_notices', 'n');
        if (null !== ShopUtil::$shopGroupId) {
            $sql->where('n.id_shop_group='.ShopUtil::$shopGroupId);
        }
        if (null !== ShopUtil::$shopId) {
            $sql->where('n.id_shop='.ShopUtil::$shopId);
        }

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Add notice.
     *
     * @param string $type        type of notice.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     * @param mixed  $args        additional args.
     *
     * @void
     */
    public static function addNotice($type, $shopGroupId, $shopId, $args = array())
    {
        if (! in_array($type, self::$coreNotices, true)) {
            $key           = uniqid('bx_', false);
            $value         = serialize($args);
            \Db::getInstance()->execute(
                "INSERT INTO `"._DB_PREFIX_."bx_notices` (`id_shop_group`, `id_shop`, `key`, `value`)
                VALUES ('.$shopGroupId.', '.$shopId.', '".pSQL($key)."', '".pSQL($value)."')"
            );
        } else {
            $alreadyExists = self::hasNotice($type, $shopGroupId, $shopId);

            if (! $alreadyExists) {
                $value         = serialize($args);
                \Db::getInstance()->execute(
                    "INSERT INTO `"._DB_PREFIX_."bx_notices` (`id_shop_group`, `id_shop`, `key`, `value`)
                    VALUES ('.$shopGroupId.', '.$shopId.', '".pSQL($type)."', '".pSQL($value)."')"
                );
            }
        }
    }

    /**
     * Remove notice.
     *
     * @param string $key         notice key.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     *
     * @void
     */
    public static function removeNotice($key, $shopGroupId, $shopId)
    {
        $sql = 'DELETE IGNORE FROM `'._DB_PREFIX_.'bx_notices` 
                WHERE ';
        if (null === $shopGroupId) {
            $sql .= '`id_shop_group` IS NULL ';
        } else {
            $sql .= '`id_shop_group`='.$shopGroupId.' ';
        }
        if (null === $shopId) {
            $sql .= 'AND `id_shop` IS NULL ';
        } else {
            $sql .= 'AND `id_shop`='.$shopId.' ';
        }
        $sql .= 'AND `key`="'.$key.'";';

        \Db::getInstance()->execute($sql);
    }

    /**
     * Whether there are active notices.
     *
     * @return boolean
     */
    public static function hasNotices()
    {
        $notices = self::getNoticeKeys();

        return !empty($notices);
    }

    /**
     * Whether given notice is active.
     *
     * @param string $noticeKey   notice key.
     * @param int    $shopGroupId shop group id.
     * @param int    $shopId      shop id.
     *
     * @return boolean
     */
    public static function hasNotice($noticeKey, $shopGroupId, $shopId)
    {

        $sql = new \DbQuery();
        $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
        $sql->from('bx_notices', 'n');
        if (null === $shopGroupId) {
            $sql->where('n.id_shop_group IS NULL');
        } else {
            $sql->where('n.id_shop_group='.$shopGroupId);
        }

        if (null === $shopId) {
            $sql->where('n.id_shop IS NULL');
        } else {
            $sql->where('n.id_shop='.$shopId);
        }
        $sql->where('n.key="'.pSQL($noticeKey).'"');
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
            'DELETE FROM `'._DB_PREFIX_.'bx_notices`;'
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
            'DELETE FROM `'._DB_PREFIX_.'bx_notices`
            WHERE `id_shop_group`="'.ShopUtil::$shopGroupId.'" AND `id_shop`="'.ShopUtil::$shopId.'";'
        );
    }
}

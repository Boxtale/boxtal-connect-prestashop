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
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return array $notices instances of notices.
     */
    public static function getNoticeInstances($shopGroupId, $shopId)
    {
        $notices = self::getNoticeKeys($shopGroupId, $shopId);
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
                        self::removeNotice($key, $shopGroupId, $shopId);
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
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return array of notice keys.
     */
    public static function getNoticeKeys($shopGroupId, $shopId)
    {
        if (null === $shopGroupId && null === $shopId) {
            $sql = new \DbQuery();
            $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
            $sql->from('bx_notices', 'n');
        } else {
            $sql = new \DbQuery();
            $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
            $sql->from('bx_notices', 'n');
            $sql->where('n.id_shop='.$shopId.' AND n.id_shop_group='.$shopGroupId);
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
            if (null === $shopGroupId && null === $shopId) {
                \Db::getInstance()->execute(
                    "INSERT INTO `"._DB_PREFIX_."bx_notices` (`key`, `value`)
                    VALUES ('".pSQL($key)."', '".pSQL($value)."')"
                );
            } else {
                \Db::getInstance()->execute(
                    "INSERT INTO `"._DB_PREFIX_."bx_notices` (`id_shop_group`, `id_shop`, `key`, `value`)
                    VALUES ('".$shopGroupId."', '".$shopId."', '".pSQL($key)."', '".pSQL($value)."')"
                );
            }
        } else {
            $alreadyExists = self::hasNotice($type, $shopGroupId, $shopId);

            if (! $alreadyExists) {
                if (!empty($args)) {
                    $value         = serialize($args);
                    if (null === $shopGroupId && null === $shopId) {
                        \Db::getInstance()->execute(
                            "INSERT INTO `"._DB_PREFIX_."bx_notices` (`key`, `value`)
                            VALUES ('".pSQL($type)."', '".pSQL($value)."')"
                        );
                    } else {
                        \Db::getInstance()->execute(
                            "INSERT INTO `"._DB_PREFIX_."bx_notices` (`id_shop_group`, `id_shop`, `key`, `value`)
                            VALUES ('".$shopGroupId."', '".$shopId."', '".pSQL($type)."', '".pSQL($value)."')"
                        );
                    }
                } else {
                    if (null === $shopGroupId && null === $shopId) {
                        \Db::getInstance()->execute(
                            "INSERT INTO `"._DB_PREFIX_."bx_notices` (`key`)
                            VALUES ('".pSQL($type)."')"
                        );
                    } else {
                        \Db::getInstance()->execute(
                            "INSERT INTO `"._DB_PREFIX_."bx_notices` (`id_shop_group`, `id_shop`, `key`)
                            VALUES ('".$shopGroupId."', '".$shopId."', '".pSQL($type)."')"
                        );
                    }
                }
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
        if (null === $shopGroupId && null === $shopId) {
            \DB::getInstance()->execute(
                'DELETE IGNORE FROM `'._DB_PREFIX_.'bx_notices` 
                WHERE `id_shop_group` IS NULL AND `id_shop` IS NULL AND `key`="'.$key.'";'
            );
        } else {
            \DB::getInstance()->execute(
                'DELETE IGNORE FROM `'._DB_PREFIX_.'bx_notices` 
                WHERE `id_shop_group`="'.$shopGroupId.'" AND `id_shop`="'.$shopId.'" AND `key`="'.$key.'";'
            );
        }
    }

    /**
     * Whether there are active notices.
     *
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @return boolean
     */
    public static function hasNotices($shopGroupId, $shopId)
    {
        $notices = self::getNoticeKeys($shopGroupId, $shopId);

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
        if (null === $shopGroupId && null === $shopId) {
            $sql = new \DbQuery();
            $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
            $sql->from('bx_notices', 'n');
            $sql->where('n.id_shop_group IS NULL AND n.id_shop IS NULL AND n.key="'.pSQL($noticeKey).'"');
            $result = \Db::getInstance()->executeS($sql);
        } else {
            $sql = new \DbQuery();
            $sql->select('n.key, n.value, n.id_shop, n.id_shop_group');
            $sql->from('bx_notices', 'n');
            $sql->where('n.id_shop_group="'.$shopGroupId.'" AND n.id_shop="'.$shopId.'" AND n.key="'.pSQL($noticeKey).'"');
            $result = \Db::getInstance()->executeS($sql);
        }

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
     * @param int $shopGroupId shop group id.
     * @param int $shopId      shop id.
     *
     * @void
     */
    public static function removeAllNoticesForShop($shopGroupId, $shopId)
    {
        \DB::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'bx_notices`
            WHERE `id_shop_group`="'.pSQL($shopGroupId).'" AND `id_shop`="'.pSQL($shopId).'";'
        );
    }
}

<?php
/**
 * Contains code for the notice controller class.
 */

namespace Boxtal\BoxtalPrestashop\Controllers\Misc;

use Boxtal\BoxtalPrestashop\Notice\CustomNotice;

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
                $classname = 'Boxtal\BoxtalPrestashop\Notice\\';
                if (! in_array($key, self::$coreNotices, true)) {
                    $value = unserialize($notice['value']);
                    if (false !== $value) {
                        $class             = new CustomNotice($key, $value);
                        $noticeInstances[] = $class;
                    } else {
                        self::removeNotice($key);
                    }
                } else {
                    $classname .= ucwords(str_replace('-', '', $key)).'Notice';
                    if (class_exists($classname, true)) {
                        $value = unserialize($notice['value']);
                        if (false !== $value && null !== $value) {
                            $class = new $classname($key, $value);
                        } else {
                            $class = new $classname($key);
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
        $sql->select('n.key, n.value');
        $sql->from('bx_notices', 'n');

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Add notice.
     *
     * @param string $type type of notice.
     * @param mixed  $args additional args.
     *
     * @void
     */
    public static function addNotice($type, $args = array())
    {
        if (! in_array($type, self::$coreNotices, true)) {
            $key           = uniqid('bx_', false);
            $value         = serialize($args);
            \Db::getInstance()->execute(
                "INSERT INTO `"._DB_PREFIX_."bx_notices` (`key`, `value`)
                VALUES ('".pSQL($key)."', '".pSQL($value)."')"
            );
        } else {
            $notices = self::getNoticeKeys();
            $alreadyExists = false;
            foreach ($notices as $notice) {
                if ($notice['key'] === $type) {
                    $alreadyExists = true;
                }
            }

            if (! $alreadyExists) {
                if (!empty($args)) {
                    \Db::getInstance()->execute(
                        "INSERT INTO `"._DB_PREFIX_."bx_notices` (`key`, `value`)
                    VALUES ('".pSQL($type)."', '".pSQL(serialize($args))."')"
                    );
                } else {
                    \Db::getInstance()->execute(
                        "INSERT INTO `"._DB_PREFIX_."bx_notices` (`key`)
                    VALUES ('".pSQL($type)."')"
                    );
                }
            }
        }
    }

    /**
     * Remove notice.
     *
     * @param string $key notice key.
     *
     * @void
     */
    public static function removeNotice($key)
    {
        \DB::getInstance()->execute(
            'DELETE IGNORE FROM `'._DB_PREFIX_.'bx_notices` WHERE `key`="'.$key.'";'
        );
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
     * @param string $noticeKey notice key.
     *
     * @return boolean
     */
    public static function hasNotice($noticeKey)
    {
        $notices = self::getNoticeKeys();
        foreach ($notices as $notice) {
            if ($noticeKey === $notice['key']) {
                return true;
            }
        }

        return false;
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
}

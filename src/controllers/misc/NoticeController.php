<?php
/**
 * Contains code for the notice controller class.
 */

namespace Boxtal\BoxtalPrestashop\Controllers\Misc;

use Boxtal\BoxtalPrestashop\Notice\CustomNotice;
use function PHPSTORM_META\map;

/**
 * Notice controller class.
 *
 * Controller for notices.
 *
 * @class       NoticeController
 * @package     Boxtal\BoxtalPrestashop\Controllers\Misc
 * @category    Class
 * @author      API Boxtal
 */
class NoticeController {

    /**
     * Array of notices - name => callback.
     *
     * @var array
     */
    private static $coreNotices = array( 'update', 'setupWizard', 'pairing', 'pairingUpdate' );

    /**
     * Get notices.
     *
     * @return array $notices instances of notices.
     */
    public static function getNotices() {
        $sql = new \DbQuery();
        $sql->select('n.key, n.value');
        $sql->from('bx_notices', 'n');
        $notices = \Db::getInstance()->executeS($sql);
        $noticeInstances = array();
        if (is_array($notices)) {
            foreach ( $notices as $notice ) {
                $key = $notice['key'];
                $classname = 'Boxtal\BoxtalPrestashop\Notice\\';
                if ( ! in_array( $key, self::$coreNotices, true ) ) {
                    $value = unserialize($notice['value']);
                    if ( false !== $value ) {
                        $class             = new CustomNotice( $key, $value );
                        $noticeInstances[] = $class;
                    } else {
                        self::removeNotice( $key );
                    }
                } else {
                    $classname .= ucwords( str_replace( '-', '', $key ) ) . 'Notice';
                    if ( class_exists( $classname, true ) ) {
                        $value = unserialize($notice['value']);
                        if ( false !== $value && null !== $value) {
                            $class = new $classname( $key, $value );
                        } else {
                            $class = new $classname( $key );
                        }
                        $noticeInstances[] = $class;
                    }
                }
            }
        }
        return $noticeInstances;
    }

    /**
     * Add notice.
     *
     * @param string $type type of notice.
     * @param mixed  $args additional args.
     * @void
     */
    public static function addNotice( $type, $args = array() ) {
        if ( ! in_array( $type, self::$coreNotices, true ) ) {
            $key           = uniqid( 'bx_', false );
            $value         = serialize($args);
            \Db::getInstance()->execute(
                "INSERT INTO `". _DB_PREFIX_ ."bx_notices` (`key`, `value`)
                VALUES ('".pSQL($key)."', '".pSQL($value)."')"
            );
        } else {
            $sql = new \DbQuery();
            $sql->select('n.key');
            $sql->from('bx_notices', 'n');
            $notices = \Db::getInstance()->executeS($sql);

            $alreadyExists = false;
            foreach ($notices as $notice) {
                if ($notice['key'] === $type) {
                    $alreadyExists = true;
                }
            }

            if ( ! $alreadyExists ) {
                if (!empty($args)) {
                    \Db::getInstance()->execute(
                        "INSERT INTO `". _DB_PREFIX_ ."bx_notices` (`key`, `value`)
                    VALUES ('".pSQL($type)."', '".pSQL(serialize($args))."')"
                    );
                } else {
                    \Db::getInstance()->execute(
                        "INSERT INTO `". _DB_PREFIX_ ."bx_notices` (`key`)
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
     * @void
     */
    public static function removeNotice( $key ) {
        \DB::getInstance()->execute(
            'DELETE IGNORE FROM `' . _DB_PREFIX_ . 'bx_notices` WHERE `key`="'.$key.'";'
        );
    }

    /**
     * Whether there are active notices.
     *
     * @void
     */
    public static function hasNotices() {
        $notices = self::getNotices();
        return !empty( $notices );
    }

    /**
     * Remove all notices.
     *
     * @void
     */
    public static function removeAllNotices() {
        \DB::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'bw_notices`;'
        );
    }
}


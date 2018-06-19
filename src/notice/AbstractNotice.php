<?php
/**
 * Contains code for the abstract notice class.
 */

namespace Boxtal\BoxtalPrestashop\Notice;

use Boxtal\BoxtalPrestashop\Controllers\Misc\Notice;
use Boxtal\BoxtalPrestashop\Controllers\Misc\NoticeController;

/**
 * Abstract notice class.
 *
 * Base methods for notices.
 *
 * @class       AbstractNotice
 * @package     Boxtal\BoxtalPrestashop\Notice
 */
abstract class AbstractNotice {

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
     * @var boolean
     */
    protected $autodestruct;

    /**
     * Construct function.
     *
     * @param string $key key for notice.
     * @void
     */
    public function __construct( $key ) {
        $this->key = $key;
    }

    /**
     * Render notice.
     *
     * @void
     */
    public function render() {
        $notice = $this;
        if ( $notice->isValid() ) {
            $boxtal = \Boxtal::getInstance();
            include realpath( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates'
                . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'notice' . DIRECTORY_SEPARATOR . $this->template . '.php';
            if ( $notice->autodestruct ) {
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
    public function remove() {
        NoticeController::removeNotice( $this->key );
    }

    /**
     * Check if notice is still valid.
     *
     * @boolean
     */
    public function isValid() {
        return true;
    }
}
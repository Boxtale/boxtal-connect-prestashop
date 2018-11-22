<?php
/**
 * Custom notice rendering
 */

$classes = '';
switch ($notice->status) {
    case 'warning':
        $classes .= 'module_error alert alert-danger';
        break;

    case 'info':
        $classes .= 'module_warning alert alert-warning';
        break;

    case 'success':
        $classes .= 'module_confirmation conf confirm alert alert-success';
        break;

    default:
        break;
}
?>
<div class="<?php echo $classes; ?>">
    <?php echo sprintf($boxtalconnect->l('%s: %s'), $shopName, $notice->message); ?>
    <p>
        <a class="bx-hide-notice" data-key="<?php echo $notice->key; ?>" data-shop-group-id="<?php echo $notice->shopGroupId; ?>" data-shop-id="<?php echo $notice->shopId; ?>">
            <?php echo $boxtalconnect->l('Hide this notice'); ?>
        </a>
    </p>
</div>

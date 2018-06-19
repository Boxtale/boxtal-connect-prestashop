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
<div class="bootstrap">
    <div class="<?php echo $classes; ?>">
        <?php echo $notice->message; ?>
        <p>
            <a class="bw-hide-notice" rel="<?php echo $notice->key; ?>">
                <?php echo $boxtal->l( 'Hide this notice' ); ?>
            </a>
        </p>
    </div>
</div>

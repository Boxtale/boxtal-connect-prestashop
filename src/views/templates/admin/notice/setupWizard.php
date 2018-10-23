<?php
/**
 * Setup wizard notice rendering
 */
?>
<div class="module_warning alert alert-warning">
    <p><?php echo $boxtalConnect->l('Run the setup wizard to connect your shop to Boxtal.'); ?></p>
    <p>
        <a href="<?php echo $notice->onboardingLink; ?>" target="_blank">
            <?php echo $boxtalConnect->l('Connect my shop'); ?>
        </a>
    </p>
</div>


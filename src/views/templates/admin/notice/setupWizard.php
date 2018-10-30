<?php
/**
 * Setup wizard notice rendering
 */
?>
<div class="module_warning alert alert-warning">
    <p><?php echo sprintf($boxtalConnect->l('Run the setup wizard to connect your shop %s to Boxtal.'), $shopName); ?></p>
    <p>
        <a href="<?php echo $notice->onboardingLink; ?>" target="_blank">
            <?php echo $boxtalConnect->l('Connect my shop'); ?>
        </a>
    </p>
</div>


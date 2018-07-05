<?php
/**
 * Setup wizard notice rendering
 */
?>
<div class="module_warning alert alert-warning">
    <p><?php echo $boxtal->l('Boxtal install is complete. Run the setup wizard to connect your shop.'); ?></p>
    <p>
        <a href="<?php echo $notice->signupLink; ?>">
            <?php echo $boxtal->l('Run the Setup Wizard'); ?>
        </a>
    </p>
</div>


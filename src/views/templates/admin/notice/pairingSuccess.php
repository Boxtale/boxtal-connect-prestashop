<?php
/**
 * Pairing success notice rendering
 */
?>
<div class="module_confirmation conf confirm alert alert-success">
    <p><?php echo sprintf($boxtalconnect->l('Congratulations! You\'ve successfully paired your site %s with Boxtal.'), $shopName); ?></p>
    <p>
        <a class="bx-hide-notice btn btn-primary-reverse btn-outline-primary" data-key="pairing" data-shop-group-id="<?php echo $notice->shopGroupId; ?>" data-shop-id="<?php echo $notice->shopId; ?>">
            <?php echo $boxtalconnect->l('Hide this notice'); ?>
        </a>
    </p>
</div>

<?php
/**
 * Pairing update notice rendering
 */
?>
<div class="module_error alert alert-danger">
    <?php echo sprintf($boxtalconnect->l('%s: security alert: someone is trying to pair your site with Boxtal. Was it you?'), $shopName); ?>
    <button class="bx-pairing-update-validate btn btn-danger" bx-pairing-update-validate="1" href="#"><?php echo $boxtalconnect->l('yes');?></button>
    <button class="bx-pairing-update-validate btn btn-secondary" bx-pairing-update-validate="0" href="#"><?php echo $boxtalconnect->l('no');?></button>
</div>

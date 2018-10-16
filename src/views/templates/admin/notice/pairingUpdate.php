<?php
/**
 * Pairing update notice rendering
 */
?>
<div class="module_error alert alert-danger">
    <?php echo $boxtalConnect->l('Security alert: someone is trying to pair your site with Boxtal. Was it you?');?>
    <button class="bx-pairing-update-validate" bx-pairing-update-validate="1" href="#"><?php echo $boxtalConnect->l('yes');?></button>
    <button class="bx-pairing-update-validate" bx-pairing-update-validate="0" href="#"><?php echo $boxtalConnect->l('no');?></button>
</div>

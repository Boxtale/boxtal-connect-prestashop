<?php
/**
 * Pairing update notice rendering
 */
?>
<div class="bootstrap">
    <div class="module_error alert alert-danger">
        <?php echo $boxtal->l('Security alert: someone is trying to pair your site with Boxtal. Was it you?');?>
        <button class="bw-pairing-update-validate" bw-pairing-update-validate="1" href="#"><?php echo $boxtal->l('yes');?></button>
        <button class="bw-pairing-update-validate" bw-pairing-update-validate="0" href="#"><?php echo $boxtal->l('no');?></button>
    </div>
</div>

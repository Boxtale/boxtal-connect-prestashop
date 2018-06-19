<?php
/**
 * Pairing failure notice rendering
 */
?>
<div class="bootstrap">
    <div class="module_confirmation conf confirm alert alert-success">
        <?php echo $boxtal->l("Congratulations! You've successfully paired your site with Boxtal.");?>
        <p>
            <a class="bw-hide-notice" rel="pairing">
                <?php echo $boxtal->l("Hide this notice");?>
            </a>
        </p>
    </div>
</div>

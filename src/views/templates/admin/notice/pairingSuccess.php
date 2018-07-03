<?php
/**
 * Pairing success notice rendering
 */
?>
<div class="module_confirmation conf confirm alert alert-success">
    <?php echo $boxtal->l("Congratulations! You've successfully paired your site with Boxtal.");?>
    <p>
        <a class="bx-hide-notice btn btn-primary-reverse btn-outline-primary" rel="pairing">
            <?php echo $boxtal->l("Hide this notice");?>
        </a>
    </p>
</div>

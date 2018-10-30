<?php
/**
 * Pairing failure notice rendering
 */
?>
<div class="module_error alert alert-danger">
    <p><?php echo sprintf($boxtalConnect->l('%s: pairing with Boxtal is not complete. Please check your Prestashop connector in your boxtal account for a more complete diagnostic.'), $shopName); ?></p>
</div>

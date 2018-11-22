<?php
/**
* Notice wrapper rendering
*/
?>

<script>
    var ajaxLink = "<?php echo $ajaxLink; ?>";
</script>

<div class="bootstrap bx-notice">
    <?php include $notice->template . '.php'; ?>
</div>

<form id="downloadMaintenanceContractForm" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <?php wp_nonce_field($args['action']); ?>
    <input type="hidden" name="action" value="<?= $args['action'];?>">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="contract_id">
</form>
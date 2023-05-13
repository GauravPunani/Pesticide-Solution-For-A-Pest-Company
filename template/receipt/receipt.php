<?php
$result=$args['data'];
?>

<form action="<?= admin_url('admin-post.php'); ?>" method="post">
    <?php wp_nonce_field('download_invoice'); ?>
    <input type="hidden" name="action" value="download_invoice">
    <input type="hidden" name="invoice_id" value="<?= $_GET['receipt-id']; ?>">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
    <button class="btn btn-success pull-right"><span><i class="fa fa-download"></i></span> Download PDF</button>
</form>

<?= (new Invoice)->invoice_body($result); ?>

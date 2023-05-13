<input type="hidden" name="signimgurl"   value="">

<?php if(isset($_GET['redirect_url']) && !empty($_GET['redirect_url'])): ?>
    <input type="hidden" name="page_url"   value="<?= $_GET['redirect_url']; ?>">
<?php else: ?>
    <input type="hidden" name="page_url"   value="<?= site_url().$_SERVER['REQUEST_URI']; ?>">
<?php endif; ?>

<?php if(isset($_GET['invoice_id']) && !empty($_GET['invoice_id'])): ?>
    <input type="hidden" name="invoice_id" value="<?= $_GET['invoice_id']; ?>">
<?php endif; ?>

<?php if(isset($_GET['show_receipt'])): ?>
    <input type="hidden" name="show_receipt" value="true">
<?php endif; ?>

<!-- if is part of invoice flow then add the hidden field  -->
<?php if(isset($_GET['invoice-flow']) && @$_SESSION['invoice_step']=="maintenance_plan"): ?>
    <span class="label label-primary">Invoice Step</span>
    <input type="hidden" name="invoice_step" value="maintenance_plan">
<?php endif; ?>
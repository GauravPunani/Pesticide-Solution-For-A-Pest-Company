<?php
/* Template Name: Tekcardpayment */ 

get_header();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <a class="btn btn-primary pull-right" href="<?= site_url(); ?>/invoice"><span><i class="fa fa-external-link"></i></span> Invoice Page</a>
            <?php if(isset($_GET['transaction_id']) && !empty($_GET['transaction_id'])): ?>
                <?= (new TekCard)->payment_receipt_content($_GET['transaction_id']); ?>
            <?php else: ?>
                <h3 class="text-center text-danger">No Payment Found</h3>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();
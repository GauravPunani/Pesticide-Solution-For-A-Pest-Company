<?php

/* Template Name: Client Receipt */ 

get_header();

global $wpdb;

$maintenance_offered=true;

if(!empty($_GET['receipt-id'])){
    $receipt_id=(new GamFunctions)->encrypt_data($_GET['receipt-id'],'d');

    $invoice_data=$wpdb->get_row("select * from {$wpdb->prefix}invoices where id='$receipt_id'");

    if($invoice_data){
        if($invoice_data->maintenance_offered!="offered"){
            $maintenance_offered=false;
        }
    
        $page_url_embed=[
            'redirect_url'  =>  $_SERVER['REQUEST_URI'],
            'invoice_id'    =>  $_GET['receipt-id'],
            'show_receipt'  =>  'true'
        ];
        
        $page_url_embed="?".http_build_query($page_url_embed);    
    }
}

?>

<div class="container">
    <?php (new GamFunctions)->getFlashMessage(); ?>
    <?php if($invoice_data): ?>
        <?php if(!$maintenance_offered): ?>

            <!-- MAINTEANCE OFFRING PART  -->
            <div class="jumbotron">
                <h2 class="text-center">Interested In Maintneance Plan ?</h2>

                <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="change_maintenance_offered_status">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="invoice_id" value="<?= $_GET['receipt-id'] ?>">
                    <button class="btn btn-primary"><span><i class="fa fa-download"></i></span> Not interested, show & download receipt</button>
                </form>

                <div class="panel">
                    <div class="panel-heading text-center">Please select type of maintenance plan which suits you best!</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3 sm-mb-4 text-center">
                                <a href="<?= (new Maintenance)->monthlyPageUrl().$page_url_embed; ?>" class="btn btn-primary"><span><i class="fa fa-wrench"></i></span> Monthly Maintenance</a>
                            </div>
                            <div class="col-md-3 sm-mb-4 text-center">
                                <a href="<?= (new Maintenance)->quarterlyPageUrl(). $page_url_embed; ?>" class="btn btn-info"><span><i class="fa fa-wrench"></i></span> Quarterly Maintenance</a>
                            </div>
                            <div class="col-md-3 sm-mb-4 text-center">
                                <a href="<?= (new Maintenance)->specialPageUrl().$page_url_embed; ?>" class="btn btn-danger"><span><i class="fa fa-wrench"></i></span> Special Maintenance</a>
                            </div>
                            <div class="col-md-3 sm-mb-4 text-center">
                                <a href="<?= (new Maintenance)->commercialPageUrl().$page_url_embed; ?>" class="btn btn-success"><span><i class="fa fa-wrench"></i></span> Commercial Maintenance</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- INVOICE PART  -->
            <div class="row">
                <div class="col-md-offset-2 col-md-8">
                    <?php get_template_part('/template/receipt/receipt',null,['data'=>$invoice_data]); ?>        
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="alert alert-danger">No Recipet Found!</div>
    <?php endif; ?>

</div>

<style>
@media only screen and (max-width: 600px) {
    .sm-mb-4{
        margin-bottom:5px;
    }
}

</style>

<?php
get_footer();

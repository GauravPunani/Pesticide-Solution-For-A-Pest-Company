<?php

$client_name = (new InvoiceFlow)->getClientName();
$phone_no = (new InvoiceFlow)->getClientPhoneNo();
$email = (new InvoiceFlow)->getClientEmail();
$address = (new InvoiceFlow)->getClientAddress();
?>

<div class="container res-form">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <div class="row">
                <div class="col-sm-12">
                <button type="button" class="btn btn-danger btn-sm pull-right" id="reset_invoice_page"><span><i class="fa fa-refresh"></i></span> Restart the page</button>
                </div>
            </div>
            <h3 class="page-header text-center">Add Prospect</h3>
            <form class="reset-form" id="prospectform" action="<?= admin_url('admin-post.php'); ?>" method="post">
                
                <?php wp_nonce_field('add_prospect'); ?>

                <input type="hidden" name="action" value="add_prospect">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <div class="form-group">
                    <label for="">Client Name</label>
                    <input type="text" class="form-control" name="client_name" value="<?= $client_name; ?>" required>
                </div>

                <div class="form-group">
                    <label for="">Phone</label>
                    <input type="text" class="form-control" name="phone" value="<?= $phone_no; ?>">
                </div>

                <div class="form-group">
                    <label for="">Email</label>
                    <input type="text" class="form-control" name="email" value="<?= $email; ?>">
                </div>

                <div class="form-group">
                    <label for="">Address</label>
                    <input type="text" class="form-control" name="address" value="<?= $address; ?>">
                </div>

                <div class="form-group">
                    <label for="">Business Name</label>
                    <input type="text" class="form-control" name="business_name">
                </div>

                <div class="form-group">
                    <label for="">Degree of interest</label>
                    <select name="status" class="form-control select2-field">
                        <option value="">Select</option>
                        <option value="interested">Interested</option>
                        <option value="semi_interested">Semi Interested</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Notes / Comments</label>
                    <textarea type="text" cols="5" rows="5" class="form-control" name="notes"></textarea>
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-file"></i></span> Add Prospect</button>
            </form>
        </div>
    </div>
</div>

<script>
(function($){
    $(document).ready(function(){
        $('#prospectform').validate({
            rules: {
                client_name: "required",
                status: "required",
                notes: "required"
            }
        });
    });
})(jQuery);
</script>
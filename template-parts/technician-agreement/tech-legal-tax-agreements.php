<?php 
if(isset($args['data'])): $technician_data = $args['data'] ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <form method="post" class="res-form" id="current_tech_agreement_form" action="<?= admin_url('admin-post.php'); ?>">
                <div class="col-sm-12">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                </div>
                <h3 class="text-center">Technician Contract/Agreement Form</h3>
                <input type="hidden" name="action" value="tech_taxpayer_misc_contract">
                <input type="hidden" name="signature_data" value="">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <?php wp_nonce_field('current_tech_taxpayer_misc_contract'); ?>

                <div class="form-group">
                    <label for="">First Name</label>
                    <input type="text" class="form-control" name="first_name" value="<?= @$technician_data->first_name; ?>">
                </div>
                <div class="form-group">
                    <label for="">Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="<?= @$technician_data->last_name; ?>">
                </div>
                <div class="form-group">
                    <label for="">Home address</label>
                    <input type="text" class="form-control" name="home_address" value="<?= @$technician_data->address; ?>">
                </div>
                <div class="form-group">
                    <label for="">State</label>
                    <input type="text" class="form-control" name="state" value="<?= @$technician_data->state; ?>">
                </div>
                <div class="form-group">
                    <label for="">Social Security</label>
                    <input type="text" class="form-control" name="social_security" value="<?= @$technician_data->social_security; ?>">
                </div>

                <p>Please check and read all Contract/Agreement Documents</p>

                <p><a target="_blank" href="https://www.irs.gov/pub/irs-pdf/fw9.pdf"><span><i class="fa fa-file"></i></span> Form W9</a></p>
 
                <div class="checkbox">
                <label><input type="checkbox" name="agree_checkbox">I have read all contract/agreement related documents and agree to the same.  </label>
                </div>                
                
                <button id="submit_btn" class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Details</button>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
(function($){

    $(document).ready(function(){
        $('#current_tech_agreement_form').validate({
            rules:{
                first_name : "required",
                last_name : "required",
                home_address : "required",
                social_security : "required",
                state : "required",
                agree_checkbox : "required"
            }
        });
    });

})(jQuery);
</script>

<?php
get_footer();
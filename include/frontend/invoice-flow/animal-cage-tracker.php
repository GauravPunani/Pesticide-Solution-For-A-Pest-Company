<?php

// first check if there are cages on site or not
$address = (new InvoiceFlow)->getClientAddress();
$is_cage_on_site = (new AnimalCageTracker)->isCageOnAddress($address);
if($is_cage_on_site){
    $cage_record = (new AnimalCageTracker)->getCagesOnAddress($address);
}
?>

<form id="cagesForm" action="<?= admin_url('admin-post.php'); ?>" class="res-form reset-form" method="post">

    <?php wp_nonce_field('create_cage_record'); ?>
    <input type="hidden" name="action" value="create_cage_record">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <div class="row">
         <div class="col-sm-12">
            <button type="button" class="btn btn-danger btn-sm pull-right" id="reset_invoice_page"><span><i class="fa fa-refresh"></i></span> Restart the page</button>
         </div>
      </div>    

    <h3 class="page-header text-center">Animal Cage Tracker</h3>
    <?php if(!$is_cage_on_site): ?>
        <div class="form-group">
            <label for="">Have you installed cages on client address ?</label>
            <select onchange="isCageInstalled(this)" name="is_cages_installed" class="form-control">
                <option value="">Select</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>
    <?php else: ?>
        <p><b>* Please update cages left on site after current service.</b></p>
    <?php endif; ?>

    <div class="cages_form <?= $is_cage_on_site ? '' : 'hidden'; ?>">

        <!-- Raccon Cages  -->
        <div class="form-group">
            <label for="">Raccon Cages Left On Site</label>
            <input min="0" type="number" class="form-control" name="racoon_cages" value="<?= isset($cage_record) ? $cage_record->racoon_cages : ''; ?>">
        </div>

        <!-- Squirrel Cages  -->
        <div class="form-group">
            <label for="">Squirrel Cages Left On Site</label>
            <input min="0" type="number" class="form-control" name="squirrel_cages" value="<?= isset($cage_record) ? $cage_record->squirrel_cages : ''; ?>">
        </div>
        
        <!-- Notes  -->
        <div class="form-group">
            <label for="">Notes for office</label>
            <textarea name="notes" cols="30" rows="5" class="form-control"></textarea>
        </div>
    </div>

    <button class="btn btn-primary cage_submit_btn"><span><i class="fa fa-upload"></i></span> Upload Cages Record</button>
</form>

<script>

    function isCageInstalled(ref){
        console.log('functin called');
        console.log(jQuery(ref).val());
        if(jQuery(ref).val() == "no"){
            jQuery('#cagesForm .cages_form').addClass('hidden');
            jQuery('#cagesForm .cage_submit_btn').text('Skip Animal Cage Form');
            jQuery('#cagesForm input[name="action"]').val('skip_cage_form');
        }
        else{
            jQuery('#cagesForm .cages_form').removeClass('hidden');
            jQuery('#cagesForm .cage_submit_btn').text('Upload Cages Record');
            jQuery('#cagesForm input[name="action"]').val('create_cage_record');
        }
    }

    (function($){
        $(document).ready(function(){

            $('#cagesForm').validate({
                rules: {
                    is_cages_installed: "required",
                    racoon_cages: "required",
                    squirrel_cages: "required",
                    notes: "required"
                }
            });
        });
    })(jQuery);
</script>
<?php

$vehicle_data = $args['data'];
?>
<form id="update_mileage_fields_form" class="res-form" action="<?= admin_url('admin-post.php');?>" method="post">

    <?php (new GamFunctions)->getFlashMessage(); ?>
    <?php wp_nonce_field('update_mileage_fields_information'); ?>

    <input type="hidden" name="action" value="update_mileage_fields_information">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <h3 class="page-header">Update Mileage Information</h3>
    <h5>Please update your mileage information accordingly to the fields</h5>

    <div class="notice notice-error">
        <p><b>IMPORTANT : </b> Please make sure to enter correct information as this data will be used to calculate vehicle next oil change / break pad change.</p>
    </div>
    <div class="form-group">
        <label for="">Current Mileage</label>
        <input maxlength="7" type="text" class="form-control numberonly" name="current_mileage">
    </div>

    <div class="form-group">
        <label for="">Oil Change Mileage (mileage at the time of last oil change)</label>
        <input maxlength="7" type="text" class="form-control numberonly" name="oil_change_mileage">
    </div>

    <div class="form-group">
        <label for="">Break Pad Change Mileage (mileage at the time of last break pad change)</label>
        <input maxlength="7" type="text" class="form-control numberonly" name="break_pad_change_mileage">
    </div>

    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Fields</button>

</form>

<script>
    (function($){
        $(document).ready(function(){
            $('#update_mileage_fields_form').validate({
                rules: {
                    current_mileage: {
                        required: true,
                        digits: true
                    },
                    oil_change_mileage: {
                        required: true,
                        digits: true
                    },
                    break_pad_change_mileage: {
                        required: true,
                        digits: true
                    },
                }
            });
        });
    })(jQuery);
</script>
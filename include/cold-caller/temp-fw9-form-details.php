<form id="tempFw9InformationForm" action="<?= admin_url('admin-post.php') ?>" method="post" class="res-form">

    <?php wp_nonce_field('temp_update_fw9_form_fields'); ?>
    <input type="hidden" name="action" value="temp_update_fw9_form_fields">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <h3 class="page-header">Fw9 Details</h3>
    <p>You're required to fill this form in order to generate your fw9 form by system.</p>

    <div class="form-group">
        <label for="">Address (number, street, and apt. or suite no.) </label>
        <input type="text" class="form-control" name="address" required>
    </div>

    <div class="form-group">
        <label for="">City, state, and ZIP code</label>
        <input type="text" class="form-control" name="city_state_zipcode" required>
    </div>

    <div class="form-group">
        <label for="">Social security number</label>
        <input maxlength="9" type="text" class="form-control" name="social_security_number" required>
    </div>    

    <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Information</button>
    
</form>

<script>
    (function($){
        $(document).ready(function(){
            $('#tempFw9InformationForm').validate({
                address: "required",
                city_state_zipcode: "required",
                social_security_number: "required",
            });
        })
    })(jQuery);
</script>
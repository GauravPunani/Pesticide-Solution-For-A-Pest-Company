<?php

$vehicle_data = $args['data'];
?>
<form id="update_parking_address_form" class="res-form" action="<?= admin_url('admin-post.php');?>" method="post">

    <?php (new GamFunctions)->getFlashMessage(); ?>
    <?php wp_nonce_field('update_parking_address_information'); ?>

    <input type="hidden" name="action" value="update_parking_address_information">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <h3 class="page-header">Update Parking Address</h3>
        
    <div class="form-group">
        <label for="parking_address">Parking Address Of Vehicle</label>
        <input type="text" id="parking_address" class="form-control" name="parking_address">
    </div>

    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Address</button>

</form>
<script>
    const parking_address = document.getElementById('parking_address');
    let autocomplete_parking_address;

    // intialise map from google-autocomplete-address.js
    initMap('parking_address', (err, autoComplete) => {
        autoComplete.addListener('place_changed', function() {
            let place = autoComplete.getPlace();
            parking_address.value = place.formatted_address;
            autocomplete_parking_address = parking_address.value;
        });
    });

    (function($){
        $('#update_parking_address_form').validate({
            rules: {
                parking_address: "required",
            },
            submitHandler: function(form){
                if(autocomplete_parking_address !== parking_address.value){
                    alert('Please make sure address is selected from suggessted address');
                    return false;
                }

                return true;
            }            
        })
    })(jQuery);

</script>
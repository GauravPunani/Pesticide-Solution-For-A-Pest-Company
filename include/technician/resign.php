<?php

$vehicle_id = $args['user']->vehicle_id;
?>

<h3 class="page-header">Technician Resignation Form</h3>
<?php (new GamFunctions)->getFlashMessage(); ?>

<form method="post" id="resignation_form" action="<?= admin_url('admin-post.php'); ?>">

    <?php wp_nonce_field('technician_resign'); ?>
    
    <input type="hidden" name="action" value="technician_resign">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <div class="form-group">
        <label for="">Please explain reason for resignation?</label>
        <textarea name="resignation_reason" cols="30" rows="5" class="form-control"></textarea>
    </div>

    <?php if($vehicle_id && (new CarCenter)->getVehicleOwner($vehicle_id) == 'company'): ?>
    <div class="form-group">
        <label for="">Please provide the parking address where you left the company vehicle.</label>
        <input type="text" class="form-control" name="parking_address" id="parking_address">
    </div>
    <?php endif; ?>

    <p class="text-danger">IMPORTANT NOTICE</p>
    <p class="text-danger">* As soon as you submit this form , you'll no longer be able to access your dashboard.</p>

    <button class="btn btn-primary"><span><i class="fa fa-ban"></i></span> Confirm Resignation</button>
</form>


<script>
    const parking_address = document.getElementById('parking_address');
    let autocomplete_parking_address;

    (function($){
        $(document).ready(function(){

            initMap('parking_address', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    parking_address.value = place.formatted_address;
                    autocomplete_parking_address = parking_address.value;
                });
            });


            $('#resignation_form').validate({
                rules:{
                    resignation_reason:"required",
                    parking_address:"required"
                },
                submitHandler: function(){
                    if(!confirm('Please confirm again by pressing on confirm button')) return false;

                    const input_parking_address = $('#resignation_form input[name="parking_address"]').val();
                    if(input_parking_address != autocomplete_parking_address)
                        return alert('Please make sure parking address is selected from suggessted address');                    

                    return true;
                }
            });
        })
    })(jQuery);
</script>
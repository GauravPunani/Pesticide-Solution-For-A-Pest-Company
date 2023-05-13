<?php

$freely_parked_vehicles = (new CarCenter)->getFreelyParkedVehicles();
$technician_id = (new Technician_details)->get_technician_id();
$vehicle = (new CarCenter)->getVehicleByTechnicianId($technician_id);
(new GamFunctions)->getFlashMessage();
?>

<form class="res-form" id="create_new_vehicle_form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

    <h3 class="page-header text-center">Create/Link Vehicle</h3>

    <?php wp_nonce_field('create_link_vehicle'); ?>
    <input type="hidden" name="action" value="create_link_vehicle">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <div class="col-sm-12">
        <div class="form-group">
            <label for="">Whose vehicle you're declaring?</label>
            <label class="radio-inline"><input type="radio" name="vehicle_owner" value="technician">Self</label>
            <label class="radio-inline"><input type="radio" name="vehicle_owner" value="company">Company</label>
        </div>
    </div>

    <div id="office-vehicle" class="hidden">
        <div class="col-sm-12">
            <div class="form-group">
                <label for="">Select Office Vehicle which is assigned to you</label>
                <select name="vehicle_id" class="form-control select2-field" required>
                    <option value="">Select</option>
                    <?php if(is_array($freely_parked_vehicles) && count($freely_parked_vehicles) > 0): ?>
                        <?php foreach($freely_parked_vehicles as $freely_parked_vehicle): ?>
                            <option value="<?= $freely_parked_vehicle->id; ?>"><?= $freely_parked_vehicle->year." ".$freely_parked_vehicle->make." ".$freely_parked_vehicle->model; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="">Enter Parking Address where you're going to park vehicle</label>
                <input type="text" class="form-control" name="office_vehicle_parking_address" id="office_vehicle_parking_address">
            </div>            
        </div>
    </div>

    <div id="technician-vehicle" class="hidden">
        <?php get_template_part('template-parts/car-center/create-vehicle-fields'); ?>
    </div>
                
    <?php if($vehicle && $vehicle->owner == 'company'): ?>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="">Enter the currently assigned vehicle (<?= $vehicle->year." ".$vehicle->make." ".$vehicle->model; ?>) parking address where you left the vehicle for office.</label>
                <input type="text" class="form-control" name='old_vehicle_parking_address' id='old_vehicle_parking_address'>
            </div>
        </div>
    <?php endif; ?>

    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create/Link Vehicle</button>
</form>

<script>

    const parking_address = document.getElementById('parking_address');
    let autocomplete_parking_address;

    const office_vehicle_parking_address = document.getElementById('office_vehicle_parking_address');
    let autocomplete_parking_address_new;

    const old_vehicle_parking_address = document.getElementById('old_vehicle_parking_address');
    let autocomplete_old_vehicle_parking_address;

    (function($){

        $(document).ready(function(){

            // intialise map from google-autocomplete.js
            initMap('parking_address', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    parking_address.value = place.formatted_address;
                    autocomplete_parking_address = parking_address.value;
                });
            });

            initMap('office_vehicle_parking_address', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    office_vehicle_parking_address.value = place.formatted_address;
                    autocomplete_parking_address_new = office_vehicle_parking_address.value;
                });
            });

            if($('#old_vehicle_parking_address').length){
                initMap('old_vehicle_parking_address', (err, autoComplete) => {
                    autoComplete.addListener('place_changed', function() {
                        let place = autoComplete.getPlace();
                        old_vehicle_parking_address.value = place.formatted_address;
                        autocomplete_old_vehicle_parking_address = old_vehicle_parking_address.value;
                    });
                });                
            }

            $('#create_new_vehicle_form').validate({
                rules: {
                    year  : "required",
                    make  : "required",
                    model  : "required",
                    plate_number  : "required",
                    vin_number  : "required",
                    parking_address  : "required",
                    last_break_change_mileage  : {
                        required: true,
                        digits: true
                    },
                    last_oil_change_mileage  : {
                        required: true,
                        digits: true
                    },
                    current_mileage  : {
                        required: true,
                        digits: true
                    },
                    vehicle_owner: "required",
                    vehicle_id: "required",
                    office_vehicle_parking_address: 'required',
                    old_vehicle_parking_address: 'required',
                    registration_document: 'required',
                    registration_expiry_date: 'required',
                    insurance_document: 'required',
                    insurance_expiry_date: 'required',
                },
                submitHandler: function(form){

                    const whoseVehicle = $('input[name="vehicle_owner"]:checked').val()

                    if(whoseVehicle == 'technician' && autocomplete_parking_address !== parking_address.value){
                        return alert('Please make sure parking address is selected from suggessted address');
                    }

                    if(whoseVehicle == 'company' && autocomplete_parking_address_new !== office_vehicle_parking_address.value){
                        return alert('Please make sure parking address is selected from suggessted address');
                    }

                    // check if old vehicle parking address needed and selected from gogole places autocomplete
                    if(old_vehicle_parking_address && autocomplete_old_vehicle_parking_address != old_vehicle_parking_address.value){
                        return alert('Please make sure parking address is selected from suggessted address for currently used vehicle');
                    }

                    return true;
                }
            });

            $('#create_new_vehicle_form input[name="vehicle_owner"]').on('change', function(){

                const vehicle_type = $(this).val();

                if(vehicle_type === "technician"){
                    $('#technician-vehicle').removeClass('hidden');
                    $('#office-vehicle').addClass('hidden');
                }
                else{
                    $('#technician-vehicle').addClass('hidden');
                    $('#office-vehicle').removeClass('hidden');
                }

            });            
        });
    })(jQuery);
</script>
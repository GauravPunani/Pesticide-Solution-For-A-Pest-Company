<?php
$vehicle = $args['vehicle'];
$without_vehicle_technicians = isset($args['without_vehicle_technicians']) ? $args['without_vehicle_technicians'] : '';
?>

<form id="edit_vehicle_info_form" method="post" action="<?= admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
    <h3 class="page-header">Edit Vehicle</h3>
    <?php wp_nonce_field('edit_vehicle_information'); ?>
    <input type="hidden" name="action" value="edit_vehicle_information">
    <input type="hidden" name="vehicle_id" value="<?= $vehicle->id; ?>">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <table class="table table-striped">
        <tbody>
            <tr>
                <th>Year</th>
                <td><input type="text" class="form-control" name="year" value="<?= $vehicle->year; ?>"></td>
                <th>Make</th>
                <td><input type="text" class="form-control" name="make" value="<?= $vehicle->make; ?>"></td>
            </tr>
            <tr>
                <th>Model</th>
                <td><input type="text" class="form-control" name="model" value="<?= $vehicle->model; ?>"></td>
                <th>Plate Number</th>
                <td><input type="text" class="form-control" name="plate_number" value="<?= $vehicle->plate_number; ?>"></td>
            </tr>
            <tr>
                <th>VIN Number</th>
                <td><input type="text" class="form-control" name="vin_number" value="<?= $vehicle->vin_number; ?>"></td>

                <th>Parking Address</th>
                <td><input type="text" id="parking_address" class="form-control" name="parking_address" value="<?= $vehicle->parking_address; ?>"></td>                                        
            </tr>

            <tr>
            <?php if(is_admin()): ?>
                    <th>Owner</th>
                    <td>
                        <select name="owner" class="form-control select2-field">
                            <option value="">Select</option>
                            <option value="company" <?= $vehicle->owner=='company' ? 'selected' : ''; ?>>Company</option>
                            <option value="technician" <?= $vehicle->owner=='technician' ? 'selected' : ''; ?>>Technician</option>
                        </select>
                    </td>                       
            <?php endif; ?>
                <th>Color</th>
                <td><input type="text" class="form-control" name="color" value="<?= @$vehicle->color; ?>"></td>
            </tr>
        </tbody>
    </table>
    
    <table class="table table-striped table-hover">
        <caption>Vehicle Documents</caption>
        <tr>
            <th>Registration Document</th>
            <?php if(!empty($vehicle->registration_document)): ?>
                <td><a target="_blank" href="<?= $vehicle->registration_document ?>"><span><i class="fa fa-eye"></i></span> View Document</a></td>
            <?php endif; ?>
            <td>
                <input type="file" name="registration_document" class="form-control" accept="image/*" <?= empty($vehicle->registration_document) ? 'required' : ''; ?>>
            </td>
            <th>Insurance Document</th>
            <?php if(!empty($vehicle->insurance_document)): ?>
                <td><a target="_blank" href="<?= $vehicle->insurance_document ?>"><span><i class="fa fa-eye"></i></span> View Document</a></td>
            <?php endif; ?>
            <td>
                <input type="file" name="insurance_document" class="form-control" accept="image/*" <?= empty($vehicle->insurance_document) ? 'required' : ''; ?>>
            </td>
        </tr>
        <tr>
            <th>Registration Expirty Date</th>
            <td><input class="form-control" type="date" name="registration_expiry_date" value="<?= $vehicle->registration_expiry_date; ?>"></td>
            <th>Insurance Expirty Date</th>
            <td><input class="form-control"  type="date" name="insurance_expiry_date" value="<?= $vehicle->insurance_expiry_date; ?>"></td>
        </tr>
        <tr>
            <th>Pesticide Decal Proof</th>
            <?php if((new CarCenter)->isPesticideDecalApplicable($vehicle->id)): ?>
                <?php if(!empty($vehicle->pesticide_decal)): ?>
                    <td><a target="_blank" href="<?= $vehicle->pesticide_decal; ?>"><span><i class="fa fa-eye"></i></span> View Proof</a></td>
                <?php endif; ?>
                <td><input type="file" name="pesticide_decal" class="form-control" accept="image/*"></td>
            <?php else: ?>
                <td class="text-success">Not Applicable</td>
            <?php endif; ?>
        </tr>        
    </table>

    <?php if(is_admin()): ?>
    <table class="table table-striped table-hover">
        <caption>Mileage Information</caption>
        <tr>

            <th>Current Mileage</th>
            <td><input type="text" class="form-control numberonly" name="current_mileage" value="<?= $vehicle->current_mileage; ?>"></td>

            <th>Last Break Change Mileage</th>
            <td><input type="text" class="form-control numberonly" name="last_break_change_mileage" value="<?= $vehicle->last_break_change_mileage; ?>"></td>

        </tr>

        <tr>

            <th>Last Oil Change Mileage</th>
            <td><input type="text" class="form-control numberonly" name="last_oil_change_mileage" value="<?= $vehicle->last_oil_change_mileage; ?>"></td>

        </tr>
    </table>
    <?php endif; ?>

    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Vehicle</button>
</form>

<script>
    (function($){
        $(document).ready(function(){

            const parking_address = document.getElementById('parking_address');
            let autocomplete_parking_address = parking_address.value;

            // intialise map from google-autocomplete-address.js
            initMap('parking_address', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    parking_address.value = place.formatted_address;
                    autocomplete_parking_address = parking_address.value;
                });
            });
            
            $('#edit_vehicle_info_form').validate({
                rules: {
                    year: "required",
                    make: "required",
                    model: "required",
                    plate_number: "required",
                    vin_number: "required",
                    color: "required",
                    parking_address: "required",
                    owner: "required",
                    status: "required",
                    registration_expiry_date: "required",
                    insurance_expiry_date: "required",
                    last_break_change_mileage: {
                        required: true,
                        digits: true
                    },
                    last_oil_change_mileage: {
                        required: true,
                        digits: true
                    },
                    current_mileage: {
                        required: true,
                        digits: true
                    },
                    company: "required",
                    technician_id: "required",
                },
                submitHandler: function(form){
                    if(autocomplete_parking_address !== parking_address.value){
                        alert('Please make sure parking address is selected from suggessted address');
                        return false;
                    }

                    return true;
                }             
            });
        });

        
    })(jQuery);
</script>
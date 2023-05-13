<?php
$technician_id = (new Technician_details)->get_technician_id();
$vehicle_id = (new CarCenter)->getTechnicianVehicleId( $technician_id );
$vehicle = (new CarCenter)->getVehicleById( $vehicle_id ); 
?>

<form method="post" class="res-form" action="<?= admin_url('admin-post.php'); ?>">

    <?php wp_nonce_field('confirm_current_vehicle'); ?>

    <input type="hidden" name="action" value="confirm_current_vehicle">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <div class="form-group">
        <label for="">Please confirm if you're using this vehicle ?</label>
        <p>Vehicle :- <?= $vehicle->year." ".$vehicle->make." ".$vehicle->model." (".$vehicle->plate_number.")"; ?></p>
        <select name="confirm_vehicle" class="form-control">
            <option value="yes">Yes, I'm using the same vehicle</option>
            <option value="no">No, I'm using another vehicle</option>
        </select>
    </div>
    <button class="btn btn-primary"><span><i class="fa fa-check"></i></span> Confirm & Submit</button>
</form>

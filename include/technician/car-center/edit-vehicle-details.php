<?php
$technician_id = (new Technician_details)->get_technician_id();
$vehicle_id = (new CarCenter)->getTechnicianVehicleId( $technician_id );
$vehicle_details=(new CarCenter)->getVehicleById( $vehicle_id );
?>

<div class="row">
    <div class="col-sm-12">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <?php get_template_part('template-parts/vehicle/edit-vehicle-form', null, ['vehicle' => $vehicle_details]); ?>
    </div>
</div>

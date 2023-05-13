<?php

if(empty($args['vehicle_id'])) return;

$vehicle_id = $args['vehicle_id'];

$vehicle_data = (new CarCenter)->getVehicleById($vehicle_id);
if(!$vehicle_data) return false;

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <button onclick="javascript:window.history.back()" class="btn btn-primary"><span><i class="fa fa-arrow-left"></i></span> Back</button>

                    <?php get_template_part('template-parts/car-center/vehicle-info', null, ['vehicle' => $vehicle_data]); ?>

                </div>
            </div>
            

        </div>
    </div>
</div>
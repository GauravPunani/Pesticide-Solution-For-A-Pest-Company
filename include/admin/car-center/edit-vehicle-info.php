<?php
global $wpdb;

$vehicle = $wpdb->get_row("
select V.*,TD.slug as technician_slug, TD.id as technician_id, TD.first_name, TD.last_name from 
{$wpdb->prefix}vehicles V
left join {$wpdb->prefix}technician_details TD
on V.id = TD.vehicle_id
where V.id='{$args['data']}' 
");

$columns = ['id','first_name','last_name','slug'];
$without_vehicle_technicians = (new Technician_details)->getWithoutVehicleTechnicians($columns);

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">

                    <?php if($vehicle): ?>
                        <?php (new GamFunctions)->getFlashMessage(); ?>

                        <a class="btn btn-primary pull-right" href="javascript:window.history.back()"><span><i class="fa fa-arrow-left"></i></span> Go Back</a>

                        <?php get_template_part('template-parts/vehicle/edit-vehicle-form', null, ['vehicle' => $vehicle, 'without_vehicle_technicians' => $without_vehicle_technicians]); ?>
                        
                    <?php else: ?>
                        <h3>No Vehicle Deails found</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
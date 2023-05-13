<?php

global $wpdb;

if(isset($_GET['vehicle-id']) && !empty($_GET['vehicle-id'])){
    get_template_part('/include/admin/car-center/edit-vehicle-info',null,['data'=>$_GET['vehicle-id']]);
    return;
}

$conditions=[];

$conditions[]=" (TD.vehicle_id IS NULL or TD.vehicle_id='')";

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" TD.branch_id IN ($accessible_branches)";
}

if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id']!="all"){
    $branch = esc_html($_GET['branch_id']);
    $conditions[] = " TD.branch_id = '$branch'";
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$vehicles=$wpdb->get_results("
    select V.* 
    from {$wpdb->prefix}vehicles V
    left join {$wpdb->prefix}technician_details TD
    on V.id = TD.vehicle_id
    $conditions
");
?>


<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
            <?php (new Navigation)->vehicle_navigation(@$_GET['vehicle_tab']); ?>
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="card-title">Vehicles Information</h3>
                    <h5 class="text-muted"><?= count($vehicles) ?> results found for the selected branch</h5>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Make</th>
                                <th>Model</th>
                                <th>Plate No.</th>
                                <th>VIN No.</th>
                                <th>Parking Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($vehicles) && count($vehicles)>0): ?>
                                <?php foreach($vehicles as $vehicle): ?>
                                    <tr>
                                        <td><?= $vehicle->year; ?></td>
                                        <td><?= $vehicle->make; ?></td>
                                        <td><?= $vehicle->model; ?></td>
                                        <td><?= $vehicle->plate_number; ?></td>
                                        <td><?= $vehicle->vin_number; ?></td>
                                        <td><?= $vehicle->parking_address; ?></td>        
                                        <td>
                                            <a class="btn btn-primary" href="<?= $_SERVER['REQUEST_URI']; ?>&vehicle-id=<?= $vehicle->id; ?>"><span><i class="fa fa-edit"></i></span> Edit</a>
                                            <button onclick="deleteVehicle(<?= $vehicle->id; ?>,this)" class="btn btn-danger"><span><i class="fa fa-trash"></i></span></button>
                                        </td>
                                    </tr>
                                        <input type="hidden" name="all_vechile" value="<?= $vehicle->technician_id; ?>">
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">No Record Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
    })(jQuery);

    function deleteVehicle(vehicle_id, ref){

        if(confirm('Are you sure you want to delete this vehicle? it will also unlink the vehicle from the linked technician profile')){
            jQuery.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                data:{
                    vehicle_id: vehicle_id,
                    action: "delete_vehicle_ajax",
                },
                dataType: "json",
                beforeSend: function(){
                    jQuery(ref).attr('disabled',true);
                },
                success: function(data){
                    if(data.status == "success"){
                        jQuery(ref).parent().parent().fadeOut();
                    }
                    else{
                        alert('Something went wrong, please try again later');
                        jQuery(ref).attr('disabled',false);
                    }
                },
                error: function (request, status, error) {
                    // alert(request.responseText);
                    alert('Something went wrong, please try again later');
                    jQuery(ref).attr('disabled',false);                
                }
            });
        }

    }

</script>
<?php

$technician_id = (new Technician_details)->get_technician_id();
$vehicle_id = (new CarCenter)->getTechnicianVehicleId($technician_id);
$vehicle_data = (new CarCenter)->getVehicleById($vehicle_id);

?>

<div class="row">
    <div class="col-sm-12">
        <?php if($vehicle_data): ?>
            <form enctype="multipart/form-data" method="post" id="break_pad_change_form" action="<?= admin_url('admin-post.php'); ?>" class="res-form">
                <h2 class="form-head text-center">Break Pads Change Proof</h2>
                <?php (new GamFunctions)->getFlashMessage(); ?>

                <?php wp_nonce_field('upload_break_pads_change_proof'); ?>
                <input type="hidden" name="action" value="upload_break_pads_change_proof">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                
                <div class="alert alert-info" role="alert">
                    <span class="badge"><i class="fa fa-info"></i></span> You're supposed to upload break pad proof only after you get the vehicle break pads changed. You need to note down the the mileage at the time of break pad change so it can be used for your next break pad change time caluclation
                </div>

                <div class="form-group">
                    <label for="">Break Pads Change Mileage (mileage at the time of break pads change)</label>
                    <input type="text" name="break_pad_change_mileage" class="form-control numberonly">
                </div>

                <div class="form-group">
                    <label for=""><span><i class="fa fa-upload"></i></span> Mileage Proof</label>
                    <input type="file" name="mileage_proof" class="form-control">
                </div>

                <div class="form-group">
                    <label for=""><span><i class="fa fa-upload"></i></span> Upload Break Pads Change Proof</label>
                    <input type="file" name="breadkpad_proof" class="form-control">
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Break Pads Change Proof</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                <p>No Vehicle linked to your account , Please add a new vehicle by <a href="<?= site_url()."/technician-dashboard/?view=vehicle-details&cnw=true" ?>">Clicking here</a></p>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php

$technician_id = (new Technician_details)->get_technician_id();
$vehicle_id = (new CarCenter)->getTechnicianVehicleId($technician_id);
$vehicle_data = (new CarCenter)->getVehicleById($vehicle_id);

?>

<div class="row">
    <div class="col-sm-12">
        <?php if($vehicle_data): ?>
            <form enctype="multipart/form-data" method="post" id="oil_change_form" action="<?= admin_url('admin-post.php'); ?>" class="res-form">
                <h2 class="form-head text-center">Oil Change Proof</h2>
                <?php (new GamFunctions)->getFlashMessage(); ?>

                <div class="alert alert-info" role="alert">
                    <span class="badge"><i class="fa fa-info"></i></span> You're supposed to upload vehicle oil change proof only after you get the vehicle oil change done. You need to note down the the mileage at the time of oil change so it can be used for your next oil change change time caluclation
                </div>                

                <?php wp_nonce_field('upload_oil_change'); ?>
                <input type="hidden" name="action" value="upload_oil_change">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <div class="form-group">
                    <label for="">Oil Change Mileage <span><i>(mileage at the time of oil change)</i></span></label>
                    <input type="text" name="oil_change_mileage" class="form-control numberonly">
                </div>

                <div class="form-group">
                    <label for=""><span><i class="fa fa-upload"></i></span> Upload Mileage Proof</label>
                    <input type="file" name="mileage_proof" class="form-control">
                </div>

                <div class="form-group">
                    <label for=""><span><i class="fa fa-upload"></i></span> Oil Change Proof</label>
                    <input type="file" name="oil_change_proof" class="form-control">
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Oil Change Proof</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                <p>No Vehicle linked to your account , Please add a new vehicle by <a href="<?= site_url()."/technician-dashboard/?view=vehicle-details&cnw=true" ?>">Clicking here</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>
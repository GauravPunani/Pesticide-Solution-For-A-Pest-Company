<?php
$technician_id = (new Technician_details)->get_technician_id();
$vehicle_id = (new CarCenter)->getTechnicianVehicleId($technician_id);
$vehicle_data = (new CarCenter)->getVehicleById($vehicle_id);
?>

<div class="row">
    <div class="col-sm-12">
        <?php if($vehicle_data): ?>
            <form enctype="multipart/form-data" method="post" id="mileage_proof_form" action="<?= admin_url('admin-post.php'); ?>" class="res-form">
                <h2 class="form-head text-center">Mileage Proof</h2>
                <?php (new GamFunctions)->getFlashMessage(); ?>

                <?php wp_nonce_field('upload_mileage'); ?>
                <input type="hidden" name="action" value="upload_mileage">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <div class="form-group">
                    <label for="">Current Mileage</label>
                    <input type="text" name="milage" class="form-control numberonly">
                </div>

                <div class="form-group">
                    <label for=""><span><i class="fa fa-upload"></i></span> Upload Mileage Proof</label>
                    <input type="file" name="mileage_proof" class="form-control">
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Mileage</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                <p>No Vehicle linked to your account , Please add a new vehicle by <a href="<?= site_url()."/technician-dashboard/?view=vehicle-details&cnw=true" ?>">Clicking here</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>
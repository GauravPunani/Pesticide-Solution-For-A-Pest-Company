<?php

if(isset($_SESSION['vehicle_editable'])) return get_template_part('/include/technician/car-center/edit-vehicle-details');

if(!empty($_GET['cnw']) && $_GET['cnw']=="true") return get_template_part('/template-parts/car-center/vehicle-form');

$technician = $args['user'];
$vehicle_details = !empty($technician->vehicle_id) ? (new CarCenter)->getVehicleById($technician->vehicle_id) : false;

?>


<!-- Vehicle Information  -->
<?php (new GamFunctions)->getFlashMessage(); ?>

<?php if($vehicle_details): ?>

    <!-- VEHICLE INFORMATION TABLE  -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group">
            <a href="<?= $_SERVER['REQUEST_URI']; ?>&cnw=true" class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Add New Vehicle</a>
            <button class="btn btn-default" data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit Vehicle Information</button>
            </div>

            <h3 class="page-header">Vehicle Information</h3>

            <?php get_template_part('template-parts/car-center/vehicle-info', null, ['vehicle' => $vehicle_details]); ?>            
            
        </div>
    </div>

    <!-- Modal -->
    <div id="codeverification" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Vehicle Details</h4>
            </div>
            <div class="modal-body">
                <div class="error-box"></div>
                <div class="confirmation-box">
                    <form action="" id="confirmation_form">
                        <?php wp_nonce_field('insert_technician_edit_code'); ?>                        
                        <input type="hidden" name="action" value="insert_technician_edit_code">
                        <input type="hidden" name="type" value="vehicle">
                        <p>You need permission from office by requesting a code to edit vehicle information</p>
                        <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                            
                    </form>
                </div>
                <div class="verification-box hidden">
                    <form action="" id="code_verification_form">
                        <?php wp_nonce_field('verify_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="verify_technician_edit_code">
                        <input type="hidden" name="db_id" value="">
                        <input type="hidden" name="type" value="vehicle">
                        <div class="form-group">
                                <label for="">Please enter the verification code</label>
                                <input type="text" name="code" maxlength="6" class="form-control">
                        </div>
                        <button id="verification_submit_btn" class="btn btn-primary">Verify & Submit</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
            </div>

        </div>
    </div>

<?php else: ?>
    <div class="row">
        <div class="col-sm-12">
            <?php get_template_part('/template-parts/car-center/vehicle-form'); ?>
        </div>
    </div>
<?php endif; ?>

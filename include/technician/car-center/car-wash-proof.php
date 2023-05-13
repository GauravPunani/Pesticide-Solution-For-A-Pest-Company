<?php
$technician_id = (new Technician_details)->get_technician_id();
$vehicle_id = (new CarCenter)->getTechnicianVehicleId($technician_id);
$vehicle_data = (new CarCenter)->getVehicleById($vehicle_id);
?>

<div class="row">
    <div class="col-sm-12">
        <?php if($vehicle_data): ?>
            <form enctype="multipart/form-data" method="post" id="car_wash_proof_form" class="res-form">
                <h2 class="form-head text-center">Car Wash Proof</h2>
                <?php wp_nonce_field('upload_car_wash_change_proof'); ?>
                <input type="hidden" name="action" value="car_wash_upload_proof">
                
                <div class="form-group">
                    <label for=""><span><i class="fa fa-upload"></i></span> Upload Car Wash Proof</label>
                    <input type="file" class="form-control" name="car_wash_proof[]" accept="image/*"  multiple >
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Car Wash Proof</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                <p>No Vehicle linked to your account , Please add a new vehicle by <a href="<?= site_url()."/technician-dashboard/?view=vehicle-details&cnw=true" ?>">Clicking here</a></p>
            </div>
        <?php endif; ?>

    </div>
</div>
<script>    
    var ajax_url="<?= admin_url('admin-ajax.php'); ?>";

    (function($){
        $(document).ready(function(){

            $('#car_wash_proof_form').validate({
                rules: {
                    "car_wash_proof[]": "required"
                },
                submitHandler: function(form){
                    let data = new FormData($('#car_wash_proof_form')[0]);
                    $.ajax({
                        type: "post",
                        url: ajax_url,
                        dataType: "json",
                        data,
                        processData: false,
                        contentType: false,                     
                        beforeSend: function(){
                            showLoader('Please wait while submiting...');
                        },
                        success: function(data){
                            if(data.status === "success"){
                                new swal('success!', data.message, 'success')
                                .then(() => {
                                    location.reload();
                                })
                            }
                            else{
                                new swal('Oops!', data.message, 'error');
                            }
                        },
                        error: function(){
                            new swal('Oops!', 'Something went wrong, please try again later', 'error');
                        }
                    })
                }
            })
        })            
    })(jQuery);    
</script>
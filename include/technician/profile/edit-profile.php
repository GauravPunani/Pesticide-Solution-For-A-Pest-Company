<?php

global $wpdb;

$technician=$args['user'];

?>

<div class="row">
    <div class="col-sm-12">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <form id="edit_profile_form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

            <?php wp_nonce_field('technician_edit_form'); ?>
            <input type="hidden" name="action" value="edit_technician_profile">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

            <!-- Technician Information  -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <caption>Technician Information</caption>
                    <tbody>
                        <tr>
                            <th>First Name</th>
                            <td><input type="text" name="first_name" value="<?= $technician->first_name; ?>" class="form-control" required></td>
                        </tr>
                        <tr>
                            <th>Last Name</th>
                            <td><input type="text" name="last_name" value="<?= $technician->last_name; ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><input type="text" name="email" value="<?= $technician->email; ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>Date of birth</th>
                            <td><input type="date" name="dob" value="<?= !empty($technician->dob) ? $technician->dob : ''  ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>Home Address</th>
                            <td><input type="text" name="address" value="<?= $technician->address; ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>Social Security</th>
                            <td><input type="text" name="social_security" value="<?= $technician->social_security; ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>Pesticide License No.</th>
                            <td><input type="text" name="certification_id" value="<?= $technician->certification_id; ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>Pesticide License</th>
                            <?php if(!empty($technician->pesticide_license)): ?>
                                <td><a target="_blank" class="btn btn-primary" href="<?= $technician->pesticide_license; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                            <?php endif; ?>
                            <td><input class="form-control" type="file" name="pesticide_license"></td>
                        </tr>
                        <tr>
                            <th>Driver License</th>
                            <?php if(!empty($technician->driver_license)): ?>
                                <td><a target="_blank" class="btn btn-primary" href="<?= $technician->driver_license; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>                                
                            <?php endif; ?>
                            <td><input class="form-control" type="file" name="driver_license"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Information</button>

        </form>
    </div>

</div>

<script>

(function($){

    $(document).ready(function(){
        $('#edit_profile_form').validate({
            rules:{
                first_name : "required",
                last_name : "required",
                email : "required",
                dob : "required",
                address : "required",
                social_security : "required",
                vehicle_year : "required",
                vehicle_make : "required",
                vehicle_model : "required",
                plate_number : "required",
                vin_number : "required",
                parking_address_of_vehicle : "required",
            }
        });
    });

})(jQuery);

</script>
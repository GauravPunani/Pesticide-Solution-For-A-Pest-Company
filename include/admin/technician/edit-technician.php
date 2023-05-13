<?php

$technician_id = $args['id'];

global $wpdb;
$branches = (new Branches)->getAllBranches();
$technician = (new Technician_details)->getTechnicianById($technician_id);

?>

<div class="container">
    <div class="row">
        <?php (new GamFunctions)->getFlashMessage(); ?>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                            <?php wp_nonce_field('technician_edit_form'); ?>
                            <input type="hidden" name="action" value="edit_technician_details">
                            <input type="hidden" name="technician_id" value="<?= $technician_id; ?>">
                            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                            <!-- Technician Information  -->
                            <table class="table table-striped table-hover">
                                <caption>Technician Information</caption>
                                <tbody>
                                    <tr>
                                        <th>First Name</th>
                                        <td><input type="text" name="first_name" value="<?= $technician->first_name; ?>" class="form-control"></td>
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
                                </tbody>
                            </table>

                            <div class="col-sm-12">
                                <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Information</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form id="updateBranchForm" action="<?= admin_url('admin-post.php') ?>" method="post">
                    
                        <?php wp_nonce_field('update_technician_branch'); ?>
                        <input type="hidden" name="action" value="update_technician_branch">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="technician_id" value="<?= $technician_id; ?>">
                        
                        <h3 class="page-header">Update Branch</h3>
                        
                        <!-- branch  -->
                        <div class="form-group">
                            <label for="branch_id">Select Branch</label>
                            <select class="branch_id form-control select2-field" name="branch_id" required>
                                <option value="">Select</option>
                                <?php if(is_array($branches) && count($branches) > 0): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Calendar ID  -->
                        <div class="form-group">
                            <label for="">Select Technician Calendar <small><i>(from google calendar)</i></small></label>
                            <select name="calendar_id" class="form-control calendar_accounts select2-field" required>
                                <option value="">Select</option>
                            </select>
                        </div>                    

                        <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Branch</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    (function($){
        $(document).ready(function(){

            $('#updateBranchForm').validate({
                rules:{
                    branch_id: "required",
                    calendar_id: "required",
                }
            })

            $('.branch_id').select2().on('change',function(){

                const branch_id = $(this).val();
                $.ajax({
                    type:'post',
                    url:"<?= admin_url('admin-ajax.php') ?>",
                    data:{
                        branch_id,
                        action: "get_calendar_accounts",
						"_wpnonce": "<?= wp_create_nonce('get_calendar_accounts'); ?>"
                    },
                    dataType:"json",
                    beforeSend:function(){

                    },
                    success:function(data){
                        console.log(data);
                        if(data.status=="success"){
                            let accounts=data.data;
                            let accounts_option_html='<option value="">Select</option>';

                            $.each(accounts,function(key,value){
                                accounts_option_html+=`
                                    <option value='${value.id}'>${value.name}</option>
                                `;
                            });

                            $('.calendar_accounts').html(accounts_option_html);
                        }
                    }
                })
            });
        });
    })(jQuery);
</script>
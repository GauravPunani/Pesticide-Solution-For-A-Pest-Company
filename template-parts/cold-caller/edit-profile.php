<?php

if(empty($args['cold_caller_id'])) return;

$cold_caller_id = $args['cold_caller_id'];
$employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
$cold_caller = (new ColdCaller)->getColdCallerById($cold_caller_id);
if(!$cold_caller) return;

$branches = (new Branches)->getAllBranches();
$application_status = (new GamFunctions)->getAllApplicationStatus();
$account_stauts = (new GamFunctions)->getAllAccountStatus();
?>

<div class="card full_width table-responsive">
    <div class="card-body">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <h3 class="page-header">Edit Profile</h3>

        <form action="<?= admin_url('admin-post.php') ?>" method="post" id="updateProfileForm" enctype="multipart/form-data">

            <?php wp_nonce_field('update_cold_caller_data'); ?>
            <input type="hidden" name="action" value="update_cold_caller_data">
            <input type="hidden" name="cold_caller_id" value="<?= $cold_caller_id ?>">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">


            <!-- BASIC INFORMATION -->
            <table class="table table-striped table-hover">
                <caption>Basic Information</caption>
                <tbody>
                    <tr>
                        <th>Name</th>
                        <td><input name="name" type="text" class="form-control" value="<?= $cold_caller->name; ?>"></td>
                        <th>Email</th>
                        <td><input type="email" name="email" type="text" class="form-control" value="<?= $cold_caller->email; ?>"></td>
                    </tr>
                    <tr>
                        <th>Phone No.</th>
                        <td><input type="text" name="phone_no" type="text" class="form-control" value="<?= $cold_caller->phone_no; ?>"></td>
                        <th>Address</th>
                        <td><input type="text" name="address" type="text" class="form-control" value="<?= $cold_caller->address; ?>"></td>
                    </tr>
                    <tr>
                        <th>City, State, Zipcode</th>
                        <td><input type="text" name="city_state_zipcode" type="text" class="form-control" value="<?= $cold_caller->city_state_zipcode; ?>"></td>
                        <th>Social Security Number</th>
                        <td><input type="text" name="social_security_number" type="text" class="form-control" value="<?= $cold_caller->social_security_number; ?>"></td>
                    </tr>
                    <th>Branch</th>
                    <td>
                        <select name="branch_id" class="form-control">
                            <option value="">Select</option>
                            <?php if(is_array($branches) && count($branches) > 0): ?>
                                <?php foreach($branches as $branch): ?>
                                    <option value="<?= $branch->id; ?>" <?= $cold_caller->branch_id == $branch->id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>                         
                </tbody>
            </table>
            
            <?php if(is_admin()): ?>
            <!-- ACCOUNT STATUS  -->
            <table class="table table-striped table-hover">
                <caption>Account Status</caption>
                <tbody>
                    <tr>
                        <th>Account Status</th>
                        <td>
                            <select name="status" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($account_stauts) && count($account_stauts) > 0): ?>
                                    <?php foreach($account_stauts as $status): ?>
                                        <option value="<?= $status->slug; ?>" <?= $status->slug == $cold_caller->status ? 'selected' : ''; ?>><?= $status->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>       
                            </select>
                        </td>
                        <th>Application Status</th>
                        <td>
                            <select name="application_status" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($application_status) && count($application_status) > 0): ?>
                                    <?php foreach($application_status as $status): ?>
                                        <option value="<?= $status->slug; ?>" <?= $status->slug == $cold_caller->application_status ? 'selected' : ''; ?>><?= $status->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- DOCUMETNS  -->
            <table class="table table-striped table-hover">
                <caption>Documents</caption>
                <tr>
                    <th>W9 Document</th>
                    <?php if(!empty($cold_caller->w9_pdf_path)): ?>
                        <td><a target="_blank" href="<?= $upload_dir['baseurl'].$cold_caller->w9_pdf_path ?>"><span><i class="fa fa-eye"></i></span> View Document</a></td>
                    <?php else: ?>
                        <td class="text-danger">No Document Found</td>
                    <?php endif; ?>
                    <th>Driving License</th>
                    <td><input type="file" name="driving_license"></td>
                    <?php if(!empty($cold_caller->cold_caller_docs)): ?>
                        <td><a target="_blank" href="<?= $cold_caller->cold_caller_docs ?>"><span><i class="fa fa-eye"></i></span> View Document</a></td>
                    <?php endif; ?>
                </tr>
            </table>
            <?php endif; ?>
            
            <!-- EXTRA INFORMATION -->
            <table class="table table-striped table-hover">
                <caption>Extra Information</caption>
                <tbody>
                    <tr>
                        <th>Skype</th>
                        <td><input type="text" name="skype" type="text" class="form-control" value="<?= $cold_caller->skype; ?>"></td>
                        <th>Company email</th>
                        <td><input type="email" name="company_email" type="text" class="form-control" value="<?= $cold_caller->company_email; ?>"></td>
                    </tr>
                </tbody>
            </table>

            <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Profile</button>
        </form>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#updateProfileForm').validate({
                rules: {
                    name: 'required',
                    email: 'required',
                    phone_no: 'required',
                    address: 'required',
                    city_state_zipcode: 'required',
                    social_security_number: 'required',
                    branch_id: 'required',
                    skype: 'required',
                    company_email: 'required',
                }
            })
        })
    })(jQuery);
</script>
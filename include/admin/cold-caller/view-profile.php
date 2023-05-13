<?php

if(empty($args['cold_caller_id'])) return;

$cold_caller_id = sanitize_text_field($args['cold_caller_id']);

if(!$cold_caller_id) return;

$cold_caller = (new ColdCaller)->getColdCallerById($cold_caller_id);
if(!$cold_caller) return;

$employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller->id, 2);

$assigned_roles = (new ColdCallerRoles)->getColdCallerRoles($employee_id);
$upload_dir = wp_upload_dir();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Cold Caller Profile</h3>
                    <table class="table table-striped table-hover">
                        <caption>Basic Information</caption>
                        <tbody>
                            <tr>
                                <th>Name</th>
                                <td><?= $cold_caller->name; ?></td>
                                <th>Email</th>
                                <td><?= $cold_caller->email; ?></td>
                            </tr>
                            <tr>
                                <th>Phone No.</th>
                                <td><?= $cold_caller->phone_no; ?></td>
                                <th>Address</th>
                                <td><?= $cold_caller->address; ?></td>
                            </tr>
                            <tr>
                                <th>City, State, Zipcode</th>
                                <td><?= $cold_caller->city_state_zipcode; ?></td>
                                <th>Social Security Number</th>
                                <td><?= $cold_caller->social_security_number; ?></td>
                            </tr>
                            <th>Branch</th>
                            <?php if(!empty($cold_caller->branch_id)): ?>
                                <td><?= (new Branches)->getBranchName($cold_caller->branch_id); ?></td>
                            <?php else: ?>
                                <td>Not Assigned yet</td>
                            <?php endif; ?>                            
                        </tbody>
                    </table>
                    <table class="table table-striped table-hover">
                        <caption>Account Status</caption>
                        <tbody>
                            <tr>
                                <th>Account Status</th>
                                <td><?= $cold_caller->status; ?></td>
                                <th>Application Status</th>
                                <td><?= $cold_caller->application_status; ?></td>                            
                            </tr>                            
                        </tbody>
                    </table>
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
                            <?php if(!empty($cold_caller->cold_caller_docs)): ?>
                                <td><a target="_blank" href="<?= $cold_caller->cold_caller_docs ?>"><span><i class="fa fa-eye"></i></span> View Document</a></td>
                            <?php else: ?>
                                <td class="text-danger">No Document Found</td>
                            <?php endif; ?>
                        </tr>
                    </table>
                    <table class="table table-striped table-hover">
                        <caption>Extra Information</caption>
                        <tbody>
                            <tr>
                                <th>Skype</th>
                                <td><?= $cold_caller->skype; ?></td>
                                <th>Company email</th>
                                <td><?= $cold_caller->company_email; ?></td>
                            </tr>
                            <tr>
                                <th>Assigned Roles</th>
                                <td colspan="3">
                                    <?php if(is_array($assigned_roles) && count($assigned_roles) > 0): ?>
                                        <?php foreach($assigned_roles as $role): ?>
                                            <?= $role->role_name.", "; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-danger">No Role Assigned</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php

if(isset($_SESSION['commercial_maintenance_editable'])){
    if(isset($_SESSION['commercial_maintenance_editable']['id']) && !empty($_SESSION['commercial_maintenance_editable']['id'])){
        require_once "edit.php";
        return;
    }
}


global $wpdb;
$contract=$wpdb->get_row("
    select CM.*, C.tracking_name, C.tracking_phone_no 
    from {$wpdb->prefix}commercial_maintenance CM
    left join {$wpdb->prefix}callrail C
    on CM.callrail_id = C.id  
    where CM.id='{$_GET['maintenance-id']}'
");

$branch_name = (new Branches)->getBranchName($contract->branch_id);

$upload_dir=wp_upload_dir();


?>

<?php if($contract): ?>
    <div class="row">
        <p class="text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit Contract</button></p>

        <div class="col-md-offset-2 col-md-8">
            <div class="table-responsive">
                <h3 class="text-center">Commercial Maintenance Contract</h3>
                <table class="table table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>Client Location</th>
                            <td><?= $branch_name; ?></td>
                        </tr>
                        <tr>
                            <th>Establishment Name</th>
                            <td><?= $contract->establishement_name; ?></td>
                        </tr>
                        <tr>
                            <th>Person In Charge</th>
                            <td><?= $contract->person_in_charge; ?></td>
                        </tr>
                        <tr>
                            <th>Client Address</th>
                            <td><?= $contract->client_address; ?></td>
                        </tr>
                        <tr>
                            <th>Establishment Phone No.</th>
                            <td><?= $contract->establishment_phoneno; ?></td>
                        </tr>
                        <tr>
                            <th>Res. Person In Charge Phone No.</th>
                            <td><?= $contract->res_person_in_charge_phone_no; ?></td>
                        </tr>
                        <tr>
                            <th>Client Email</th>
                            <td><?= $contract->client_email; ?></td>
                        </tr>
                        <tr>
                            <th>Cost Per Visit</th>
                            <td><?= $contract->cost_per_visit; ?></td>
                        </tr>
                        <tr>
                            <th>Frequency of visit</th>
                            <td><?= $contract->frequency_of_visit; ?></td>
                        </tr>
                        <tr>
                            <th>Frequency per</th>
                            <td><?= $contract->frequency_per; ?></td>
                        </tr>
                        <tr>
                            <th>Prefered Days</th>
                            <td><?= $contract->prefered_days; ?></td>
                        </tr>
                        <tr>
                            <th>Prefered Time</th>
                            <td><?= $contract->prefered_time; ?></td>
                        </tr>
                        <tr>
                            <th>Contract Start Date</th>
                            <td><?= $contract->contract_start_date; ?></td>
                        </tr>
                        <tr>
                            <th>Contract End Date</th>
                            <td><?= $contract->contract_end_date; ?></td>
                        </tr>
                        <tr>
                            <th>Date Created</th>
                            <td><?= $contract->date_created; ?></td>
                        </tr>
                        <tr>
                            <th>Contract PDF</th>
                            <?php if(!empty($contract->pdf_path)): ?>
                                <td><a target="_blank" href="<?= $upload_dir['baseurl'].$contract->pdf_path; ?>" class="btn btn-info"><span><i class="fa fa-eye"></i></span> View</a></td>
                            <?php else: ?>
                                <td><p class="text-danger">Not Available</p></td>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <th>Callrail No.</th>
                            <td><?= $contract->tracking_phone_no ."-". $contract->tracking_name; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="codeverification" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Commercial Contract</h4>
            </div>
            <div class="modal-body">
                <div class="error-box"></div>
                <div class="confirmation-box">
                    <form action="" id="confirmation_form">
                        <?php wp_nonce_field('insert_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="insert_technician_edit_code">
                        <input type="hidden" name="type" value="commercial_maintenance">
                        <input type="hidden" name="id" value="<?= $_GET['maintenance-id']; ?>">
                        <input type="hidden" name="name" value="<?= trim($user->first_name." ".$user->last_name); ?>">
                        <p>You need permission from office by requesting a code to edit contract</p>
                        <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                            
                    </form>
                </div>
                <div class="verification-box hidden">
                    <form action="" id="code_verification_form">
                        <?php wp_nonce_field('verify_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="verify_technician_edit_code">
                        <input type="hidden" name="id" value="<?= $_GET['maintenance-id']; ?>">
                        <input type="hidden" name="db_id" value="">
                        <input type="hidden" name="type" value="commercial_maintenance">
                        <input type="hidden" name="name" value="<?= trim($user->first_name." ".$user->last_name); ?>">
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


    
<?php endif; ?>
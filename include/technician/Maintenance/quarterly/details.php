<?php

if(isset($_SESSION['quarterly_maintenance_editable']) && !empty($_SESSION['quarterly_maintenance_editable'])){
    if($_SESSION['quarterly_maintenance_editable']['id']==$_GET['maintenance-id']){
        get_template_part('/include/technician/Maintenance/quarterly/edit');
        return;
    }
}

global $wpdb;
$technician_id=(new Technician_details)->get_technician_id();
$contract=$wpdb->get_row("select * from {$wpdb->prefix}maintenance_contract where id='{$_GET['maintenance-id']}' and technician_id='$technician_id'");

$upload_dir=wp_upload_dir();

?>

<?php if($contract): ?>
    <div class="col-md-offset-2 col-md-8">
        <p class="text-right"><button class="btn btn-primary"  data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit</button></p>
        <div class="table-responsive">
            <h3 class="text-center">Quarterly Maintenance Contract</h3>
            <table class="table table-striped table-hover">
                <tbody>
                    <tr>
                        <th>Client Name</th>
                        <td><?= $contract->client_name; ?></td>
                    </tr>
                    <tr>
                        <th>Client Address</th>
                        <td><?= $contract->client_address; ?></td>
                    </tr>
                    <tr>
                        <th>Client Phone No.</th>
                        <td><?= $contract->client_phone_no; ?></td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td><?= $contract->client_email; ?></td>
                    </tr>
                    <tr>
                        <th>Cost Per Quarter</th>
                        <td><?= $contract->cost_per_month; ?></td>
                    </tr>
                    <tr>
                        <th>Maintenance Charges Interval</th>
                        <td><?= ucwords($contract->charge_type); ?></td>
                    </tr>
                    <tr>
                        <th>Total Cost</th>
                        <td><?= $contract->total_cost; ?></td>
                    </tr>
                    <tr>
                        <th>Contract Start Date</th>
                        <td><?= date('d M Y',strtotime($contract->contract_start_date)); ?></td>
                    </tr>
                    <tr>
                        <th>Contract End Date</th>
                        <td><?= date('d M Y',strtotime($contract->contract_end_date)); ?></td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <td><?= date('d M Y',strtotime($contract->date)); ?></td>
                    </tr>
                    <tr>
                        <th>Contract Pdf</th>
                        <?php if(!empty($contract->pdf_path)): ?>
                            <td><a target="_blank" href="<?= $upload_dir['baseurl'].$contract->pdf_path; ?>" class="btn btn-info"><span><i class="fa fa-eye"></i></span> View</a></td>
                        <?php else: ?>
                            <td><p class="text-danger">Not Available</p></td>
                        <?php endif; ?>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="codeverification" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Quarterly Maintenance</h4>
            </div>
            <div class="modal-body">
                <div class="error-box"></div>
                <div class="confirmation-box">
                    <form action="" id="confirmation_form">
                        <?php wp_nonce_field('insert_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="insert_technician_edit_code">
                        <input type="hidden" name="type" value="quarterly_maintenance">
                        <input type="hidden" name="id" value="<?= $_GET['maintenance-id']; ?>">
                        <input type="hidden" name="name" value="<?= (new Technician_details)->get_technician_name(); ?>">
                        <p>You need permission from office by requesting a code to edit maintenance plan</p>
                        <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                            
                    </form>
                </div>
                <div class="verification-box hidden">
                    <form action="" id="code_verification_form">
                        <?php wp_nonce_field('verify_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="verify_technician_edit_code">
                        <input type="hidden" name="id" value="<?= $_GET['maintenance-id']; ?>">
                        <input type="hidden" name="db_id" value="">
                        <input type="hidden" name="type" value="quarterly_maintenance">
                        <input type="hidden" name="name" value="<?= (new Technician_details)->get_technician_name(); ?>">
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
    <h3 class="text-center">No Maintenance Contract Found</h3>
<?php endif; ?>


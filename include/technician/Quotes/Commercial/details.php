<?php
global $wpdb;

if(!empty($_SESSION['commercial_quote_editable']['id']) && $_SESSION['commercial_quote_editable']['id'] == $_GET['quote_id']){
    $quote_id = sanitize_text_field($_GET['quote_id']);
    return get_template_part('template-parts/quotes/commercial/edit-quote', null, ['quote_id' =>$quote_id]);
}

$quote = $wpdb->get_row("
    select * 
    from {$wpdb->prefix}commercial_quotesheet 
    where id='{$_GET['quote_id']}'
");

$tech_name = (new Technician_details)->getTechnicianName($quote->technician_id);
$branch = (new Branches)->getBranchName($quote->branch_id);

$upload_dir=wp_upload_dir();

?>

<?php if($quote): ?>
    <div class="row">
        <div class="col-md-offset-2 col-md-8">

            <p class="text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit Quote</button></p>
        
            <h1 class="text-center">Commercial Quote #<?= $quote->quote_no; ?></h1>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <tr>
                        <th>Client Name</th>
                        <td><?= $quote->client_name; ?></td>
                    </tr>
                    <tr>
                        <th>Client Address</th>
                        <td><?= $quote->client_address; ?></td>
                    </tr>
                    <tr>
                        <th>Decision Maker Name</th>
                        <td><?= $quote->decision_maker_name; ?></td>
                    </tr>
                    <tr>
                        <th>Client Phone</th>
                        <td><?= $quote->client_phone; ?></td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td><?= $quote->clientEmail; ?></td>
                    </tr>
                    <tr>
                        <th>Technician</th>
                        <td><?= (!empty($quote->tech_diff_name)) ? $quote->tech_diff_name : $tech_name; ?></td>
                    </tr>
                    <tr>
                        <th>Branch</th>
                        <td><?= $branch; ?></td>
                    </tr>
                </table>
                <table class="table table-striped table-hover">
                    <tr>
                            <th>NO. OF TIMES</th>
                            <td><?= $quote->no_of_times; ?></td>
                    </tr>
                    <tr>
                            <th>INITIAL COST</th>
                            <td><?= $quote->initial_cost; ?></td>
                    </tr>
                    <tr>
                            <th>COST PER VISIT</th>
                            <td><?= $quote->cost_per_visit; ?></td>
                    </tr>
                    <tr>
                            <th>QUOTE PDF</th>
                            <td><a class="btn btn-primary" target="_blank" href="<?= $upload_dir['baseurl'].$quote->pdf_path; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                    </tr>
                    <tr>
                            <th>DATE</th>
                            <td><?=  $quote->date; ?></td>
                    </tr>
                    <tr>
                            <th>CALENDAR EVENT ID</th>
                            <td><?=  $quote->calendar_event_id; ?></td>
                    </tr>
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
                <h4 class="modal-title">Edit Quote</h4>
            </div>
            <div class="modal-body">
                <div class="error-box"></div>
                <div class="confirmation-box">
                    <form action="" id="confirmation_form">
                        <?php wp_nonce_field('insert_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="insert_technician_edit_code">
                        <input type="hidden" name="type" value="commercial_quote">
                        <input type="hidden" name="id" value="<?= $_GET['quote_id']; ?>">
                        <input type="hidden" name="name" value="<?= trim($user->first_name." ".$user->last_name); ?>">
                        <p>You need permission from office by requesting a code to edit quote</p>
                        <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                            
                    </form>
                </div>
                <div class="verification-box hidden">
                    <form action="" id="code_verification_form">
                        <?php wp_nonce_field('verify_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="verify_technician_edit_code">
                        <input type="hidden" name="id" value="<?= $_GET['quote_id']; ?>">
                        <input type="hidden" name="db_id" value="">
                        <input type="hidden" name="type" value="commercial_quote">
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

<?php else:  ?>
    <h1>No Recourd found </h1>
<?php endif; ?>
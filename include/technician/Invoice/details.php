<?php 

    $user = $args['user'];

    if(isset($_SESSION['invoice_editable'])){
        if($_SESSION['invoice_editable']['id']==$_GET['invoice_id']){
            require_once "edit.php";
            return;
        }
    }

    global $wpdb;

    $invoice = $wpdb->get_row("
        select I.*,C.tracking_phone_no,C.tracking_name
        from {$wpdb->prefix}invoices I
        left join {$wpdb->prefix}callrail C
        on I.callrail_id = C.id
        where I.id = '{$_GET['invoice_id']}'
        and technician_id='$user->id'
    ");

    $tech_name = (new Technician_details)->getTechnicianName( $invoice->technician_id );
    $branch_name = (new Branches)->getBranchName( $invoice->branch_id );
?>
<?php if($invoice): ?> 

    <div class="row">
        <div class="col-md-offset-2 col-md-8">

            <p class="text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit Invoice</button></p>

            <h1 class="text-center">Invoice #<?= $invoice->id; ?></h1>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <tr>
                        <th>Client Name</th>
                        <td><?= $invoice->client_name; ?></td>
                    </tr>
                    <tr>
                        <th>Telephone number</th>
                        <td><?= $invoice->phone_no; ?></td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td><?= $invoice->address; ?></td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td><?= $invoice->date; ?></td>
                        
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= $invoice->email; ?></td>
                    </tr>
                    <tr>
                        <th>Service fee</th>
                        <td>$<?= $invoice->service_fee; ?></td>
                    </tr>
                </table>
                <table class="table table-striped table-hover">
                    <tr class="invoice_mid_tbl_tr">
                        <th></th>
                        <th>Units</th>
                        <th>Price Per Unit</th>
                        <th>Total</th>
                    </tr>
                    <?php $product=json_decode($invoice->product_used,true);?>
                    <?php if(array_key_exists('0',(array)$product)): ?>
                        <?php foreach($product as $key=> $val): ?>
                        <tr>
                            <th class="table-hd"><?= $val['name']; ?></th>
                            <td><?= $val['Unit']; ?></td>
                            <td>$<?= $val['Price']; ?></td>
                            <td>$<?= $val['Total']; ?></td>
                        </tr>    
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <th class="table-hd">Bait station</th>
                            <td><?= $product['bail_stationUnit']; ?></td>
                            <td><?= $product['bail_stationPrice']; ?></td>
                            <td>   <?= $product['bail_stationTotal']; ?></td>
                        </tr>

                        <tr>
                        <th class="table-hd">Glue boards</th>
                                <td>   <?= $product['glue_boardUnit']; ?></td>
                                <td>   <?= $product['glue_boardPrice']; ?></td>
                                <td>   <?= $product['glue_boardTotal']; ?></td>
                        </tr>
                        <tr>
                        <th class="table-hd">Mouse snap traps</th>
                            <td>   <?= $product['mouse_snapUnit']; ?></td>
                                <td>   <?= $product['mouse_snapPrice']; ?></td>
                                <td>   <?= $product['mouse_snapTotal']; ?></td>
                        </tr>
                        <tr>
                        <th class="table-hd">Rat snap traps</th>
                                <td><?= $product['rat_snapUnit']; ?></td>
                                <td><?= $product['rat_snapPrice']; ?></td>
                                <td><?= $product['rat_snapTotal']; ?></td>
                        </tr>
                        <tr>
                        <th class="table-hd">Hole sealing</th>
                                <td><?= $product['hole_sealUnit']; ?></td>
                                <td><?= $product['hole_sealPrice']; ?></td>
                                <td>   <?= $product['hole_sealTotal']; ?></td>
                        </tr><tr>
                        <th class="table-hd">Tin cats</th>
                        <td>   <?= $product['tin_catsUnit']; ?></td>
                                <td>   <?= $product['tin_catsPrice']; ?></td>
                                <td>   <?= $product['tin_catsTotal']; ?></td>
                        </tr> 
                        <tr>
                        <th class="table-hd">Poison</th>
                                <td>   <?= $product['poisonUnit']; ?></td>
                                <td>   <?= $product['poisonPrice']; ?></td>
                                <td>   <?= $product['poisonTotal']; ?></td>
                        </tr>
                        <tr>
                        <th class="table-hd">Fogging</th>
                        <td>   <?= $product['fogginUnit']; ?></td>
                                <td>   <?= $product['fogginPrice']; ?></td>
                                <td>   <?= $product['fogginTotal']; ?></td>
                        </tr>
                        <tr>
                            <th class="table-hd">Other</th>
                            <td><?= $product['other_unit']; ?></td>
                            <td><?= $product['other_price']; ?></td>
                            <td><?= $product['other_total']; ?></td> 
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Tax</th>
                        <td>$<?= $invoice->tax; ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Total Amount</th>
                        <td>$<?= $invoice->total_amount; ?></</td>
                    </tr>

                </table>
                <?php $upload_dir=wp_upload_dir(); ?>
                <table class="table table-striped table-hover">
                    <tr>
                        <th>Payment Method</th>
                        <td><?= ucwords(str_replace('_',' ', $invoice->payment_method)); ?></td>
                    </tr>
                    <tr>
                        <th>Technician</th>
                        <td><?= $tech_name; ?></td>
                    </tr>
                    <tr>
                        <th>Branch</th>
                        <td><?= $branch_name; ?></td>
                    </tr>
                    <tr>
                        <th>Type of Service Provided</th>
                        <td><?= $invoice->type_of_service_provided; ?></td>
                    </tr>
                    <tr>
                        <th>Callrail Tracking Number</th>
                        <td><?= $invoice->tracking_phone_no; ?></td>
                    </tr>
                    <tr>
                        <th>Callrail Tracking Name</th>
                        <td><?= $invoice->tracking_name; ?></td>
                    </tr>

                    <tr>
                        <th>Client Signature</th>
                    </tr>
                    <tr>
                        <th>Additional Doc</th>
                        <?php if(!empty($invoice->additional_doc)): ?>
                                <td><a target="_blank" href="<?= $invoice->additional_doc; ?>">View Document</a></td>
                        <?php else: ?>
                                <td>No Additional Doc</td>
                        <?php endif; ?>
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
                <h4 class="modal-title">Edit Invoice</h4>
            </div>
            <div class="modal-body">
                <div class="error-box"></div>
                <div class="confirmation-box">
                    <form action="" id="confirmation_form">
                        <?php wp_nonce_field('insert_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="insert_technician_edit_code">
                        <input type="hidden" name="type" value="invoice">
                        <input type="hidden" name="id" value="<?= $_GET['invoice_id']; ?>">
                        <input type="hidden" name="name" value="<?= trim($user->first_name." ".$user->last_name); ?>">
                        <p>You need permission from office by requesting a code to edit invoice</p>
                        <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                            
                    </form>
                </div>
                <div class="verification-box hidden">
                    <form action="" id="code_verification_form">
                        <?php wp_nonce_field('verify_technician_edit_code'); ?>
                        <input type="hidden" name="action" value="verify_technician_edit_code">
                        <input type="hidden" name="type" value="invoice">
                        <input type="hidden" name="id" value="<?= $_GET['invoice_id']; ?>">
                        <input type="hidden" name="db_id" value="">
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

<?php else: ?>
    
    <h1>No Record Found</h1>

<?php endif; ?>
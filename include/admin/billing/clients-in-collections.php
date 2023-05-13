<?php
$billings = $args['data'];
$total_amount_owed = 0;
if (is_array($billings) && count($billings) > 0) {
    foreach ($billings as $billing) {
        $total_amount_owed += $billing['total_amount_owed'];
    }
}
$agency_name = $proof_upload = false;
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="page-header">Total Amount Debt <b><?= (new GamFunctions)->beautify_amount_field($total_amount_owed); ?></b></h4>
                            <form>
                                <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                                <div class="form-group">
                                    <label for="">Search by client,address,phone etc...</label>
                                    <input type="text" class="form-control" name="search" value="">
                                </div>
                                <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php if (isset($_GET['search']) && !empty($_GET['search'])) : ?>
            <div class="col-sm-12">
                <p><b><?= count($billings); ?> Records Found for the keyword : <b><?= $_GET['search']; ?></b> <a href="<?= admin_url('admin.php?page=billing') ?>">Show All Records</a></b></p>
            </div>
        <?php endif; ?>

        <div class="col-sm-12">
            <?php if (is_array($billings) && count((array)$billings) > 0) : ?>
                <?php foreach ($billings as $key => $val) : ?>
                    <div class="card full_width table-responsive">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <p><strong>Client Name : </strong> <?= ucwords($val['client_name']); ?></p>
                                    <p><strong>Clinet Address :</strong> <span class="row_address_<?= $key; ?>"><?= $val['client_address']; ?></span></p>
                                    <p><strong>Phone No. : </strong> <?= $val['phone_no']; ?></p>
                                    <p><strong>Client Email :</strong> <span class="row_email_<?= $key; ?>"><?= $val['client_email']; ?></span></p>
                                    <?php if (!empty($val['debt_agency_name'])) : $agency_name = true; ?>
                                        <hr>
                                        <p><strong>Collection Agency Debt Given To : </strong> <?= ucwords($val['debt_agency_name']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-sm-6">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Invoice No.</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($val['date_of_service'] as $service) : ?>
                                                <tr>
                                                    <td><?= $service['invoice_no']; ?></td>
                                                    <td><?= date('d M Y', strtotime($service['date'])); ?></td>
                                                    <td><?= (new GamFunctions)->beautify_amount_field($service['amount']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th>$<?= $val['total_amount_owed']; ?></th>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <!-- Add Note -->
                                    <?php if (!$agency_name) : ?>
                                        <button data-toggle="modal" data-target="#collection_add_debt_note" data-invoice-ids='<?= json_encode((object) $val['invoice_id']); ?>' class="btn btn-primary update_debt_note"><span><i class="fa fa-plus"></i></span> Add Collection Agency</button>
                                    <?php endif; ?>
                                    
                                    <!-- Payment Proof -->
                                    <?php if (!empty($val['payment_proof'])) : $proof_upload = true;?>
                                        <a href="<?= $val['payment_proof']; ?>" target="_blank"><button class="btn btn-primary"><span><i class="fa fa-eye"></i></span> View Payment Proof</button></a>
                                    <?php else : ?>
                                        <button <?= (!$agency_name) ? 'disabled=disabled' : '';?> data-toggle="modal" data-target="#collection_upload_payment_proof" data-invoice-ids='<?= json_encode((object) $val['invoice_id']); ?>' class="btn btn-info upload_collection_pay_proof"><span><i class="fa fa-upload"></i></span> Upload Proof Of Payment</button>
                                    <?php endif; ?>
                                    
                                    <!-- Mark as Paid -->
                                    <?php if (!empty($val['paid_status'])) : ?>
                                        <div class="btn btn-success"><span><i class="fa fa-check"></i></span> Paid</div>
                                    <?php else : ?>
                                        <button <?= (!$proof_upload) ? 'disabled=disabled' : '';?>
                                        data-invoice-ids='<?= json_encode((object) $val['invoice_id']); ?>' class="btn btn-default update_collection_payment_status"><span><i class="fa fa-check"></i></span> Mark as Paid</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php $agency_name = $proof_upload = false;
                endforeach; ?>

                <!-- Add Note  -->
                <div id="collection_add_debt_note" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Collections agency debt given</h4>
                            </div>
                            <div class="modal-body">
                                <form id="collection_add_debt_note_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                                    <?php wp_nonce_field('update_collection_debt_note'); ?>
                                    <input type="hidden" name="action" value="update_collection_debt_note">
                                    <input type="hidden" name="invoice_all_ids" value="">
                                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                    <div class="form-group">
                                        <label for="">Add Agency Name</label>
                                        <input type="text" class="form-control" name="collection_agency_name" value="">
                                    </div>
                                    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Submit</button>
                                </form>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Upload Proof of payment  -->
                <div id="collection_upload_payment_proof" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Upload Proof Of Payment</h4>
                            </div>
                            <div class="modal-body">
                                <form id="collection_upload_payment_proof_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                                    <?php wp_nonce_field('collection_proof_of_payment_nonce'); ?>
                                    <input type="hidden" name="action" value="collection_proof_of_payment">
                                    <input type="hidden" name="invoice_all_ids" value="">
                                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                                    <div class="form-group">
                                        <label for="">Select Document</label>
                                        <input class="form-control" accept="image/png,image/jpeg,image/jpg" type="file" name="collection_payment_proof">
                                    </div>

                                    <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Proof Of Payment</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Mark as Paid  -->
                <div id="collection_mark_as_paid" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Edit Address</h4>
                            </div>
                            <div class="modal-body">
                                <form class="form-horizontal" id="invoice_address_update_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                                    <?php wp_nonce_field('update_grouped_invoices_address'); ?>
                                    <input type="hidden" name="action" value="update_grouped_invoices_address">
                                    <input type="hidden" name="actual_address" value="">
                                    <input type="hidden" name="row_id" value="">
                                    <div class="form-group">
                                        <label class="control-label col-sm-4" for="email">Enter Address</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="new_address" value="">
                                        </div>
                                    </div>
                                    <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Address</button>
                                </form>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ADDRESS MODAL END  -->
            <?php else : ?>
                <div class="card full_width">
                    <div class="card-body">
                        <p class="text-success">No Payment Pending for the location</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="fixed-alert hidden">
    <div class="alert-title">
        <h3>Processing...</h3>
    </div>
    <div class="alert-body">
    </div>
</div>
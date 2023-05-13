<?php
$billings=(new Autobilling)->get_unpaid_invoices(@$_GET['branch_id'],false)->group_unpaid_invoices();
// echo "<pre>";print_r($billings);wp_die();
?>


<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form action="<?= admin_url('admin.php'); ?>">
                        <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
                        <div class="form-group">
                            <label for="">Search by client,address,phone etc...</label>
                            <input type="text" class="form-control" name="search" value="">
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                    </form>        
                </div>
            </div>
        </div>
    
        <div class="col-md-6">
            <button class="send_multiple_mini_statements btn btn-primary pull-right" disabled><span><i class="fa fa-envelope"></i></span> Send Mini Statments</button>
        </div>
        <?php if(isset($_GET['search'])): ?>
            <div class="col-sm-12">
                <p><b><?= count($billings); ?> Records Found for the keyword : <b><?= $_GET['search']; ?></b>  <a href="<?= admin_url('admin.php?page=billing') ?>">Show All Records</a></b></p>
            </div>
        <?php endif; ?>

        <div class="col-sm-12">
            <div class="checkbox">
                <label><input type="checkbox" id="select_all_bills" value="">Select All Bills</label>
            </div>        
        </div>

        <div class="col-sm-12">
            <?php if(is_array($billings) && count((array)$billings)>0): ?>
                <?php foreach($billings as $key=>$val): ?>
                    <div class="card full_width">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="checkbox" id="mini_statement_checkbox_<?= $key; ?>" class="multi_mini_statement_checkbox" data-grouped-json='<?= json_encode($val); ?>'>

                                    <p><strong>Client Name : </strong> <?= ucwords($val['client_name']); ?></p>
                                    <p><strong>Clinet Address :</strong> <span class="row_address_<?= $key; ?>"><?= $val['client_address']; ?></span></p>
                                    <p><strong>Phone No. : </strong>  <?= $val['phone_no']; ?></p>
                                    <p><strong>Client Email :</strong>  <span class="row_email_<?= $key; ?>"><?= $val['client_email']; ?></span></p>
                                </div>
                                <div class="col-sm-6">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($val['date_of_service'] as $service): ?>
                                            <tr>
                                                <td><?= $service['id']; ?></td>
                                                <td><?= $service['date']; ?></td>
                                                <td><?= $service['amount']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th>$<?= $val['total_amount_owed']; ?></th>
                                            </tr>
                                        
                                        </tbody>
                                    </table>
                                    <button data-toggle="modal" data-target="#myModal"  data-client-name="<?= ucwords($val['client_name']); ?>" data-client-address="<?= $val['client_address']; ?>" data-client-phone-no="<?= $val['phone_no']; ?>" data-client-email="<?= $val['client_email']; ?>" data-total-amount="<?= $val['total_amount_owed']; ?>" data-service-dates='<?= json_encode($val['date_of_service']); ?>' data-invoice-ids='<?= json_encode($val['invoice_id'],JSON_UNESCAPED_SLASHES); ?>' class="btn btn-primary download_mini_statement"><span><i class="fa fa-file"></i></span> Genrate Mini Statement</button>
                                    <button data-toggle="modal" data-target="#update_email_modal" data-invoice-address='<?= $val['client_address'] ?>' data-row-id="<?= $key; ?>" class="btn btn-info update_group_email"><span><i class="fa fa-edit"></i></span> Edit Email</button>
                                    <button data-toggle="modal" data-target="#update_address_modal" data-invoice-address='<?= $val['client_address'] ?>' data-row-id="<?= $key; ?>" class="btn btn-default update_group_address"><span><i class="fa fa-edit"></i></span> Edit Address</button>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div id="myModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Generate Mini Statement</h4>

                            </div>
                            <div class="modal-body">
                            <form class="form-horizontal" id="mini_statement_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
								<?php wp_nonce_field('billing_generate_mini_statement'); ?>
                                <input type="hidden" name="action" value="billing_generate_mini_statement">
                                <input type="hidden" name="invoice_ids" value="">
                                <h4>Basic Details</h4>
                                <div class="form-group">
                                    <label class="control-label col-sm-4" for="email">Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="name" class="form-control" placeholder="Enter Name" value="Abdul Wadood">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4" for="totalamount">Total Amount:</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="total_amount" class="form-control" placeholder="Enter total amount" value="391">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4" for="address">Address</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="address" class="form-control" placeholder="Enter address" value="">
                                    </div>
                                </div>
                                <h4>Invoice Dates</h4>
                                <div class="invoice-dates">
                                            
                                </div>
                                <button class="btn btn-primary"><span><i class="fa fa-file"></i></span> Generate Mini Statement</button>


                            </form>

                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                        </div>

                    </div>
                </div>

                <!-- MODALS  -->
                <div id="update_email_modal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Edit Email</h4>

                            </div>
                            <div class="modal-body">
                            <form class="form-horizontal" id="invoice_email_update_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
								<?php wp_nonce_field('update_grouped_invoices_email'); ?>
                                <input type="hidden" name="action" value="update_grouped_invoices_email">
                                <input type="hidden" name="invoice_address" value="">
                                <input type="hidden" name="row_id" value="">
                                <div class="form-group">
                                    <label class="control-label col-sm-4" for="email">Enter Email</label>
                                    <div class="col-sm-8">
                                        <input type="email" name="email" class="form-control" placeholder="test@gmail.com" value="">
                                    </div>
                                </div>
                                <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Email</button>
                            </form>

                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                        </div>

                    </div>
                </div>
                <!-- ADDRESS MODAL  -->
                <div id="update_address_modal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Edit Address</h4>

                            </div>
                            <div class="modal-body">
                            <form class="form-horizontal" id="invoice_address_update_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
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
            <?php else: ?>
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
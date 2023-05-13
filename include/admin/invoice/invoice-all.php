<?php
if (!empty($_GET['invoice_id'])) {
    if (!empty($_GET['action']) && $_GET['action'] == "edit") {
        return get_template_part('/include/admin/invoice/invoice-edit');
    } else {
        return get_template_part('/include/admin/invoice/invoice-view');
    }
}

global $wpdb;

$payment_methods = (new TekCard)->paymentMethods();
$branches = (new Branches)->getAllBranches();
$tech_leads = (new GamFunctions)->gamTechnicianLeadsBasedOnCalendarCode();

$conditions = [];
$branch_id = "";

if (!current_user_can('other_than_upstate')) {
    $accessible_branches = (new Branches)->partner_accessible_branches(true);
    $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

    $conditions[] = " branch_id IN ($accessible_branches)";
}

if (!empty($_GET['branch_id'])) {
    $conditions[] = " branch_id = '{$_GET['branch_id']}'";
}

if (!empty($_GET['technician_id'])) {
    $technician_id = urldecode($_GET['technician_id']);
    $conditions[] = " technician_id = '$technician_id'";
}

if (!empty($_GET['payment_method'])) {
    $conditions[] = " payment_method = '{$_GET['payment_method']}'";
}

if (!empty($_GET['label'])) {
    $conditions[] = " invoice_label = '{$_GET['label']}'";
}

if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $conditions[] = " DATE(date) >='{$_GET['date_from']}' and DATE(date)<='{$_GET['date_to']}'";
    $conditions[] = " (callrail_id IS NULL or callrail_id='' or callrail_id='unknown')";
}

if (isset($_GET['from_date']) && !empty($_GET['from_date'])) {
    $conditions[] = " DATE(date) >= '{$_GET['from_date']}'";
}
if (isset($_GET['to_date']) && !empty($_GET['to_date'])) {
    $conditions[] = " DATE(date) <= '{$_GET['to_date']}'";
}

if (!empty($_GET['other_filters'])) {
    switch ($_GET['other_filters']) {
        case 'opt_out_for_maintenance':
            $conditions[] = " opt_out_for_maintenance='true' ";
            break;
        case 'clients_on_auto_billing':
            $conditions[] = " (status IS NULL or status='')  and email IS NOT NULL and  email!='' ";
            break;
        case 'no_email':
            $conditions[] = " (status IS NULL or status='') and (email IS NULL or email='') ";
            break;
        case 'unknown_leads':
            $conditions[] = " (callrail_id IS NULL or callrail_id='unknown' or callrail_id='') ";
            break;
        case 'error_sending_email':
            $conditions[] = " email_status='not_sent'";
            break;
    }
}


//if isset status=unchecked, show the unchecked record first by setting the ORDER BY in mysql query
if (!empty($_GET['status'])) {
    $status = urldecode($_GET['status']);
    $conditions[] = " (status='$status' or status IS NULL)";
    $orderby = "ORDER BY `client_name` ASC";
} else {
    $orderby = "ORDER BY `date` DESC";
}

$conditions[] = " is_deleted != 1";

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : "";

if (!empty($_GET['search'])) {
    $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix . 'invoices');
    if (!empty($conditions)) {
        $conditions .= " " . (new GamFunctions)->create_search_query_string($whereSearch, $_GET['search'], 'and');
    } else {
        $conditions = (new GamFunctions)->create_search_query_string($whereSearch, $_GET['search']);
    }
}

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;

$total_rows = $wpdb->get_var("
        SELECT COUNT(*) FROM 
        {$wpdb->prefix}invoices 
        $conditions
    ");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$invoices = $wpdb->get_results("
        select * from 
        {$wpdb->prefix}invoices 
        $conditions 
        $orderby  
        LIMIT $offset, $no_of_records_per_page 
    ");

//calculate recorde index by page no
$records_starting_index = (($pageno - 1) * $no_of_records_per_page) + 1;

if (!empty($branch_id)) {
    $technicians = (new Technician_details)->getTechniciansByBranchId($branch_id);
} else {
    $technicians = (new Technician_details)->get_all_technicians(true, '', false);
}
?>

<?php (new GamFunctions)->getFlashMessage(); ?>

<!-- INVOICE LISTING  -->
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form action="<?= $_SERVER['REQUEST_URI']; ?>">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>" name="search" placeholder="e.g. name, email etc..">
                        </div>

                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch_id" class="form-group select2-field">
                                <option value="">All</option>
                                <?php if (is_array($branches) && count($branches) > 0) : ?>
                                    <?php foreach ($branches as $branch) : ?>
                                        <option value="<?= $branch->id; ?>" <?= (!empty($_GET['branch_id']) && $_GET['branch_id'] == $branch->id) ? 'selected' : '';  ?>><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="technician">Technician</label>
                            <select class="form-control select2-field" name="technician_id">
                                <option value="">Select</option>

                                <?php foreach ($technicians as $technician) : ?>
                                    <option value="<?= urlencode($technician->id); ?>" <?= (!empty($_GET['technician_id']) && $_GET['technician_id'] == $technician->id) ? 'selected' : ''; ?>><?= ucwords(str_replace('_', ' ', $technician->first_name . " " . $technician->last_name)); ?></option>
                                <?php endforeach; ?>
                            </select>

                        </div>

                        <div class="form-group">
                            <label for="">Payment Methods</label>
                            <select name="payment_method" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if (is_array($payment_methods) && count($payment_methods) > 0) : ?>
                                    <?php foreach ($payment_methods as $payment_method) : ?>
                                        <option value="<?= $payment_method->slug; ?>" <?= (!empty($_GET['payment_method']) && $_GET['payment_method'] == $payment_method->slug) ? 'selected' : ''; ?>><?= $payment_method->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Other Filters</label>
                            <select name="other_filters" class="form-group select2-field">
                                <option value="">Select</option>
                                <option <?= @$_GET['other_filters'] == 'opt_out_for_maintenance' ? 'selected' : ''; ?> value="opt_out_for_maintenance">Opt out for maintenance</option>
                                <option <?= @$_GET['other_filters'] == 'clients_on_auto_billing' ? 'selected' : ''; ?> value="clients_on_auto_billing">Auto Billing</option>
                                <option <?= @$_GET['other_filters'] == 'no_email' ? 'selected' : ''; ?> value="no_email">No Email</option>
                                <option <?= @$_GET['other_filters'] == 'unknown_leads' ? 'selected' : ''; ?> value="unknown_leads">Unknown Leads</option>
                                <option <?= @$_GET['other_filters'] == 'error_sending_email' ? 'selected' : ''; ?> value="error_sending_email">Error Mailing Invoice</option>
                            </select>
                        </div>

                        <!-- Date Range  -->
                        <div class="form-group">
                            <label for="from">From Date</label>
                            <input type="date" class="form-control" name="from_date" value="<?= @$_GET['from_date']?>">
                        </div>

                        <div class="form-group">
                            <label for="to">To Date</label>
                            <input type="date" class="form-control" name="to_date" value="<?= @$_GET['to_date']?>">
                        </div>

                        <div class="form-group">
                            <label for="">By Label</label>
                            <select name="label" class="form-group select2-field">
                                <option value="">Select</option>
                                <?php foreach($tech_leads as $k=>$tech) : ?>
                                <option <?= @$_GET['label'] == $k ? 'selected' : ''; ?> value="<?= $k;?>">
                                <?= $tech;?></option>
                                <?php endforeach;?>
                            </select>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Invoices</h3>

                    <div class="form-group btn-group">
                        <a role="button" class="btn btn-default" href="<?= $_SERVER['REQUEST_URI']; ?>&status=not_paid"><span><i class="fa fa-square"></i></span> Show Unchecked Invoices</a>
                        <button type="button" class="btn btn-primary openmodal genereate_statement" data-div-class="invoice-mini-basic-fields" data-model-id="ministatement"><span><i class="fa fa-file"></i></span> Generate statement of invoices satisfied</button>
                    </div>

                    <?php if (!empty($_GET['search'])) : ?>
                        <p class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page=' . $_GET['page']); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
                        </p>

                    <?php elseif (!empty($_GET['technician_id'])) : ?>
                        <?php $tech_name = (new Technician_details)->getTechnicianName($_GET['technician_id']); ?>
                        <p class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <?= $total_rows ?> Records Found for the Technician :
                            <b><?= $tech_name; ?></b>
                            <a class="btn btn-info" href="<?= admin_url('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab']); ?>">
                                <span><i class="fa fa-database"></i></span> Show All Records
                            </a>
                        </p>

                    <?php else : ?>
                        <p class="alert alert-info"><b><?= $total_rows; ?></b> Records Found</p>
                    <?php endif; ?>

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Invoice No.</th>
                                <th>Paid?</th>
                                <?php if (!empty($_GET['tab']) && $_GET['tab'] == "office_to_bill_client") : ?>
                                    <th>Office Sent Bill?</th>
                                <?php endif; ?>
                                <th>Client Name</th>
                                <th>Email</th>
                                <th>Total Amount</th>
                                <th>Payment Proof Doc</th>
                                <th>Date</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($invoices) && !empty($invoices)) : ?>
                                <?php foreach ($invoices as $invoice) : ?>
                                    <tr>
                                        <td><input type="checkbox" class="select-invoices" data-client-name="<?= $invoice->client_name; ?>" data-invoice-id="<?= $invoice->id; ?>" data-invoice-email="<?= $invoice->email; ?>" <?= @in_array($invoice->id, $_SESSION['invoice_selected_items'])  ? 'checked' : ''; ?>></td>
                                        <td><?= $invoice->invoice_no; ?></td>
                                        <td>
                                            <input type="checkbox" name="" class="invoice_paid" data-invoice-id="<?= $invoice->id; ?>" <?= $invoice->status == "paid" ? "checked" : ""; ?>>
                                        </td>
                                        <?php if (!empty($_GET['tab']) && $_GET['tab'] == "office_to_bill_client") : ?>
                                            <td><input type="checkbox" class="office_sent_bill" data-invoice-id="<?= $invoice->id; ?>" <?= $invoice->office_sent_bill == "true" ? 'checked' : ''; ?>></td>
                                        <?php endif; ?>
                                        <td><?= $invoice->client_name; ?></td>
                                        <td><?= $invoice->email; ?></td>
                                        <td>$<?= $invoice->total_amount; ?></td>
                                        <td>
                                            <?php if (!empty($invoice->additional_doc)) : ?>
                                                <a target="_blank" href="<?= $invoice->additional_doc; ?>">View payment proof</a>
                                            <?php else : ?>
                                                <?php 
                                                $office_payment_method = array("check", "office_to_bill_client", "paid_by_zelle");
                                                if (in_array($invoice->payment_method,$office_payment_method)) : ?>
                                                    <buttton class="btn btn-primary openmodal docupload" data-input-id="doc_invoice_id" data-invoice-id="<?= $invoice->id; ?>" data-model-id="docupload"><span><i class="fa fa-upload"></i></span> Upload Payment proof</buttton>
                                                <?php elseif ($invoice->payment_method == "credit_card" && $invoice->status == "paid") : ?>
                                                    <b>Verified by system</b>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($invoice->date)); ?></td>
                                        <td>
                                            <?php if (!empty($invoice->admin_note)) : ?>
                                                <p><b><?= nl2br(htmlspecialchars($invoice->admin_note)); ?></b></p>
                                            <?php else : ?>
                                                <p>-</p>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a class="mymodal" data-model-id="commentModal" data-invoice-id="<?= $invoice->id; ?>" href="javascript:void(0)"><span><i class="fa fa-comments"></i></span> Add Notes</a></li>

                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&invoice_id=<?= $invoice->id; ?>"><span><i class="fa fa-eye"></i></span> View Invoice</a></li>

                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&action=edit&invoice_id=<?= $invoice->id; ?>"><span><i class="fa fa-edit"></i></span> Edit Invoice</a></li>

                                                    <li class="hidden"><a onclick="deleteInvoice(<?= $invoice->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete</a></li>

                                                    <li><a onclick="downloadInvoice(<?= $invoice->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-download"></i></span> Download Invoice</a></li>

                                                    <li><a data-invoice-id="<?= $invoice->id; ?>" data-email="<?= $invoice->email; ?><?php echo (!empty($invoice->multiple_inv_emails) ? sprintf(',%s',implode(',',unserialize($invoice->multiple_inv_emails))) : '');?>" onclick="openEmailBox(this)" href="javascript:void(0)"><span><i class="fa fa-envelope"></i></span> Email Invoice</a></li>

                                                    <li><a data-invoice-id="<?= $invoice->id; ?>" data-model-id="listministatements" class="openmodal ministatements" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View Statements</a></li>

                                                    <li><a data-invoice-id="<?= $invoice->id; ?>" data-attach='<?= $invoice->optional_images; ?>' data-model-id="listattachments" class="openmodal attachments" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> Attachments</a></li>

                                                    <?php if (!empty($invoice->email)) : ?>
                                                        <li><a onclick="smsInvoice(<?= $invoice->id; ?>, '<?= $invoice->phone_no; ?><?php echo (!empty($invoice->multiple_inv_phone) ? sprintf(',%s',implode(',',unserialize($invoice->multiple_inv_phone))) : '');?>')" href="javascript:void(0)"><span><i class="fa fa-envelope"></i></span> SMS Invoice Link</a></li>
                                                    <?php else : ?>
                                                        <li class="disabled"><a href="javascript:void()"><span><i class="fa fa-envelope"></i></span> SMS Invoice Link (<i>No Email</i>)</a></a></li>
                                                    <?php endif; ?>

                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php $records_starting_index++; ?>

                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="12">No Record found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>
        </div>
    </div>
</div>

<form id="downloadInvoiceForm" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <?php wp_nonce_field('download_invoice'); ?>
    <input type="hidden" name="action" value="download_invoice">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="invoice_id">
</form>


<!-- COMMENT MODAL  -->
<div id="commentModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Notes</h4>
            </div>
            <div class="modal-body">
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" id="invoice_id" name="invoice_id" value="">
                    <input type="hidden" name="action" value="invoice_add_note">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="form-group">
                        <textarea name="admin_note" cols="30" rows="10" class="form-control" placeholder="Add Note here"></textarea>
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-comment"></i></span> Add Comment</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span><i class="fa fa-times"></i></span> Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Mini Statement Modal  -->
<div id="ministatement" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Generate Mini Statement</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">

                    <?php wp_nonce_field('generate_mini_statement'); ?>
                    <input type="hidden" name="action" value="generate_mini_statement">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="invoice-mini-basic-fields"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- ADDITIONAL DOC UPLOAD MODAL  -->
<div id="docupload" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Upload Additional Document</h4>
            </div>
            <div class="modal-body">
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="invoice_additional_doc">
                    <input type="hidden" id="doc_invoice_id" name="invoice_id" value="">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <div class="form-group">
                        <label for="">Select Document</label>
                        <input class="form-control" accept="image/png,image/jpeg,image/jpg" type="file" name="doc" id="additional_doc">
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Additional Doc</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- MINI STATEMENT MODAL  -->
<div id="listministatements" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Mini Statements</h4>
            </div>
            <div class="modal-body mini_statement_content">
                <p>Loading Statements...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="attachements_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Attachments </h4>
            </div>
            <div class="modal-body">
                <div class="all-attachments"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- EMAIL INVOICE MODAL  -->
<div id="invoice_email_modal" class="modal fade" rold="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><strong>Send Invoice to Email</strong></h4>
            </div>
            <div class="modal-body">
                <form id="invoice_email_form" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="send_invoice_to_email">
                    <input type="hidden" name="invoice_id" value="">
                    <div class="form-group">
                        <label for="">Enter Client Email <small style="color:#f00;">(Invoice can be send to multiple email address just seperate each email with comma like : email@gmail.com,email2@gmail.com)</small></label>
                        <input type="text" class="form-control" name="client_email" value="" required>
                    </div>
                    <button id="invoice_email_submit_btn" class="btn btn-primary"><span><i class="fa fa-envelope"></i></span> <span id="invoice_email_submit_span">Send Invoice</span></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="smsInvoiceLinkModal" class="modal fade" rold="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <p>SMS Invoice Link To Client</p>
            </div>
            <div class="modal-body">
                <form id="smsInvoiceLinkForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                    <?php wp_nonce_field('sms_invoice_link'); ?>

                    <input type="hidden" name="action" value="sms_invoice_link">
                    <input type="hidden" name="invoice_id" value="">
                    <div class="form-group">
                        <label for="">Client Phone No.</label>
                        <input type="text" class="form-control" name="phone_no" value="" placeholder="e.g. +1123-456-7890" required>
                    </div>
                    <button id="sms_invoice_btn" class="btn btn-primary"><span><i class="fa fa-envelope"></i></span> <span id="invoice_email_submit_span">SMS Invoice Link</span></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadInvoice(invoice_id) {
        jQuery('#downloadInvoiceForm input[name="invoice_id"]').val(invoice_id);
        jQuery('#downloadInvoiceForm').submit();
    }

    function smsInvoice(invoice_id, phone_no) {
        jQuery('#smsInvoiceLinkForm input[name="invoice_id"]').val(invoice_id);
        jQuery('#smsInvoiceLinkForm input[name="phone_no"]').val(phone_no);
        jQuery('#smsInvoiceLinkModal').modal('show');
    }

    function deleteInvoice(invoice_id, ref) {
        swal.fire({
                title: "Are you sure",
                text: "You want to delete this invoice ?",
                showCancelButton: true,
                confirmButtonText: 'Yes, I am sure!',
                icon: "warning",
            })
            .then((willDelete) => {
                if (willDelete.isConfirmed) {
                    jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        dataType: "json",
                        data: {
                            action: "delete_invoice",
                            invoice_id,
                            '_wpnonce': "<?= wp_create_nonce('delete_invoice'); ?>"
                        },
                        beforeSend: function() {
                            jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                            showLoader('Deleting invoice in system, please wait...');
                        },
                        success: function(data) {
                            if (data.status === "success") {
                                swal.close();
                                location.reload();
                            } else {
                                swal.fire(
                                    'Oops!',
                                    data.message,
                                    'error'
                                );
                                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                            }
                        }
                    })
                }
            });
    }

    (function($) {
        $(document).ready(function() {
            $('#smsInvoiceLinkForm').validate({
                rules: {
                    phone_no: "required"
                },
                submitHandler: function(form) {
                    jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: $('#smsInvoiceLinkForm').serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $('#sms_invoice_btn').attr('disabled', true);
                        },
                        success: function(data) {
                            alert(data.message);
                            $('#sms_invoice_btn').attr('disabled', false);
                            $('#smsInvoiceLinkModal').modal('hide');
                        }
                    })
                }
            });
        });

    })(jQuery);

    (function($) {
        $(document).on('click', '.attachments', function() {

            let attachments = $(this).attr('data-attach');
            attach_html = '';

            if (attachments != "") {
                attach_html += generateDocsHtml(attachments);
            } else {
                attach_html += "<p class='text-danger'>No Attachments Found</p>";
            }

            $('.all-attachments').html(attach_html);
            $('#attachements_modal').modal('show');
        });
    })(jQuery);
</script>
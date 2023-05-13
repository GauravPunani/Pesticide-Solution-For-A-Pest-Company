<?php
if (!empty($_GET['email_id'])) return get_template_part('/include/admin/email/view-call-logs');

if (isset($_GET['edit-id']) && !empty($_GET['edit-id'])) {
    get_template_part('/include/admin/email/edit-email', null, ['data' => $_GET['edit-id']]);
    return;
}

global $wpdb;

//  Filter
$branches = (new Branches)->getAllBranches();

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

if (isset($_GET['tab']) && $_GET['tab'] == "non-reocurring") {
    $conditions[] = " status='non_reocurring'";
} elseif (isset($_GET['tab']) && $_GET['tab'] == "reocurring") {
    $conditions[] = " status='reocurring'";
} elseif (isset($_GET['tab']) && $_GET['tab'] == "cold-calls") {
    $conditions[] = " status='cold_calls'";
} elseif (isset($_GET['tab']) && $_GET['tab'] == "book-appointment") {
    $conditions[] = " book_appointment ='1'";
} elseif (isset($_GET['other_filters']) && $_GET['other_filters'] == "booked") {
    $conditions[] = " book_appointment ='1'";
} elseif (isset($_GET['other_filters']) && $_GET['other_filters'] == "not_book") {
    $conditions[] = " book_appointment ='0'";
}

// check spring treatment in get request
if (isset($_GET['spring']) && $_GET['spring'] == "yes") {
    $conditions[] = " answer='yes'";
} elseif (isset($_GET['spring']) && $_GET['spring'] == "no") {
    $conditions[] = " answer='no'";
} elseif (isset($_GET['spring']) && $_GET['spring'] == "no-ans") {
    $conditions[] = " email_receive IS NOT NULL AND answer IS NULL";
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : "";

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page = 50;

//calculate recorde index by page no
$records_starting_index = (($pageno - 1) * $no_of_records_per_page) + 1;

if (!empty($branch_id)) {
    $technicians = (new Technician_details)->getTechniciansByBranchId($branch_id);
} else {
    $technicians = (new Technician_details)->get_all_technicians(true, '', false);
}

// check in get request non reocurring
$show_action_btn = false;
if (isset($_GET['tab']) && !empty($_GET['tab']) && $_GET['tab'] == 'non-reocurring') {
    $show_action_btn = true;
}

if (isset($_GET['search'])) {
    $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix . 'emails');

    if (!empty($conditions)) {
        $conditions .= " " . (new GamFunctions)->create_search_query_string($whereSearch, trim($_GET['search']), 'and');
    } else {
        $conditions = (new GamFunctions)->create_search_query_string($whereSearch, trim($_GET['search']));
    }
}

$offset = ($pageno - 1) * $no_of_records_per_page;
$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}emails
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$emails = $wpdb->get_results("
    select *
    from {$wpdb->prefix}emails
    $conditions
    order by date desc
    LIMIT $offset, $no_of_records_per_page
");

$coldcalls = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}cold_calls_log
    LEFT JOIN {$wpdb->prefix}emails
    ON {$wpdb->prefix}cold_calls_log.cold_call_id = {$wpdb->prefix}emails.id ORDER BY {$wpdb->prefix}emails.id desc"
);

$all_campaigns = (new Emails)->getClientsCampaign();

$unsubscribe_clients = (new Emails)->getUnsubscribeClientsList();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="page-header"><strong>Filters</strong></h2>
                    <form id="client_adv_filters" action="<?= $_SERVER['REQUEST_URI']; ?>">
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
                            <label for="">Select Client</label>
                            <select name="tab" class="form-group select2-field">
                                <option value="">Select</option>
                                <option <?= (!empty($_GET['tab']) && $_GET['tab'] == 'reocurring') ? 'selected' : '';  ?> value="reocurring">Reocurring</option>
                                <option <?= (!empty($_GET['tab']) && $_GET['tab'] == 'non-reocurring') ? 'selected' : '';  ?> value="non-reocurring">Non-Reocurring</option>
                                <option <?= (!empty($_GET['tab']) && $_GET['tab'] == 'cold-calls') ? 'selected' : '';  ?> value="cold-calls">Cold Calls</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Status</label>
                            <select name="other_filters" class="form-group select2-field">
                                <option value="">Select Status</option>
                                <option <?= @$_GET['other_filters'] == 'booked' ? 'selected' : ''; ?> value="booked">Booked</option>
                                <option <?= @$_GET['other_filters'] == 'not_booked' ? 'selected' : ''; ?> value="not_booked">Not Booked</option>
                            </select>
                        </div>

                        <?php if ($show_action_btn) : ?>
                            <div class="form-group">
                                <label for="">Need spring treatment ?</label>
                                <select name="spring" class="form-group select2-field">
                                    <option value="">Select</option>
                                    <!-- <option value="all-clients">All Clients</option> -->
                                    <option <?= (!empty($_GET['spring']) && $_GET['spring'] == 'yes') ? 'selected' : '';  ?> value="yes">Yes</option>
                                    <option <?= (!empty($_GET['spring']) && $_GET['spring'] == 'no') ? 'selected' : '';  ?> value="no">No</option>
                                    <option <?= (!empty($_GET['spring']) && $_GET['spring'] == 'no-ans') ? 'selected' : '';  ?> value="no-ans">No answer</option>
                                </select>
                            </div>
                        <?php endif; ?>

                        <p><a onclick="resetFilters('client_adv_filters')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>

                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-12 col-lg-12">
            <!-- Trigger the modal with a button -->
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <!-- < ?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?> -->
                    <div class="row">

                        <div class="col-sm-3 col-md-3 col-lg-3">
                            <h2 class="page-header"><strong>Client Database</strong></h2>
                        </div>

                        <div class="col-sm-9 col-md-9 col-lg-9">
                            <div class="page-header btn-group" style="float: right;">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#email_modal"><span><i class="fa fa-plus"></i></span> Add Client</button>
                                <button type="button" data-toggle="modal" data-target="#email_csv_download_modal" class="btn btn-success"><span><i class="fa fa-file-excel-o"></i></span> Download CSV</button>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($_GET['search'])) : ?>
                        <p class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab']); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>

                            <?php if ($show_action_btn) : ?>
                                <button class="btn btn-warning select_all_clients"><span><i class="fa fa-check"></i></span> Select All</button>
                                <button class="btn btn-success client_spring_notify" disabled="disabled"><span><i class="fa fa-plane"></i></span> Send Notification</button>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <table class="table table-striped table-hover">
                        <caption><?= $total_rows; ?> Records Found</caption>
                        <thead>
                            <tr>
                                <?php if ($show_action_btn) : ?>
                                    <th></th>
                                <?php endif; ?>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone No.</th>
                                <th>Email</th>

                                <?php if ($show_action_btn) : ?>
                                    <th>Email received</th>
                                    <th>Need spring treatment</th>
                                <?php endif; ?>
                                <th>Date Created</th>
                                <?php if (isset($_GET['tab']) && $_GET['tab'] == "cold-calls") { ?><th>Notes</th> <?php } else {
                                                                                                                } ?>

                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($emails) && count($emails) > 0) : ?>
                                <?php foreach ($emails as $email) : ?>
                                    <tr>
                                        <?php if ($show_action_btn) : ?>
                                            <td>
                                                <?php if (!empty($email->email) && empty($email->answer)) : ?>
                                                    <input type="checkbox" name="spring_choose_clients[]" value='<?= (new GamFunctions)->encrypt_data(json_encode(['id' => $email->id, 'branch_id' => $email->branch_id, 'name' => $email->name, 'email' => $email->email])); ?>' class="clients_intrest_spring">
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td><?= $email->name; ?></td>
                                        <td><?= $email->address; ?></td>
                                        <td><?= $email->phone; ?></td>
                                        <td><?= $email->email; ?></td>
                                        <?php if ($show_action_btn) : ?>
                                            <th><?= (!empty($email->email_receive) ? '<span class="label label-primary">' . date('d M Y, g:i a', strtotime($email->email_receive)) . '</span>' : (!empty($email->email) ?
                                                    '<span class="label label-warning">Not sent yet</span>' : '-'
                                                )); ?></th>
                                            <th><?= (!empty($email->answer) ? '<span class="label label-' . ($email->answer == 'yes' ? 'success' : 'info') . '">' . ucfirst($email->answer) . ' on ' . date('d M Y, g:i a', strtotime($email->answer_date)) . '</span>'
                                                    : (!empty($email->email_receive) ? '<span class="label label-danger">No answer</span>' : '-')
                                                ); ?></th>
                                        <?php endif; ?>
                                        <td><?= date('d M Y', strtotime($email->date)); ?></td>
                                        <?php if (isset($_GET['tab']) && $_GET['tab'] == "cold-calls") { ?><td><?= $email->note; ?></td><?php } else {
                                                                                                                                    } ?>

                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&edit-id=<?= $email->id; ?>"><span><i class="fa fa-edit"></i></span> Edit</a></li>
                                                    <li><a onclick="updateColdCallsLogs(<?= $email->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-user-plus"></i></span> Create Call Logs</a></li>
                                                    <li><a data-cold-call-id="<?= $email->id; ?>" class="view_reocurring_modal" data-toggle="modal" data-target="#reocurring_modal"><span><i class="fa fa-eye"></i></span> Show Call Logs</a></li>

                                                    <?php if (!empty($email->email)) : ?>
                                                        <li><a onclick="banClientFromCampaign(<?= $email->id; ?>,'<?= $email->email; ?>')" href="javascript:void(0)"><span><i class="fa fa-ban"></i></span> Unsubscribe Email Notification</a></li>
                                                    <?php endif; ?>

                                                    <?php 
                                                        if (in_array($email->id, array_column($unsubscribe_clients, 'client_id'))) : ?>
                                                            <li><a data-client-id="<?= $email->id; ?>" class="view_unsubscribe_email_log" data-toggle="modal" data-target="#unsubscribe_email_list_modal"><span><i class="fa fa-eye"></i></span> View unsubscribe Notification</a></li>
                                                        <?php endif;?>
                                                </ul>

                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>

                    <!-- View Cold Call Status modal -->
                    <div id="reocurring_modal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title"><strong>View Cold Call Status</strong></h4>
                                </div>
                                <div class="modal-body stu_data">
                                </div>
                                <div class="modal-footer">
                                    <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- View Cold Call Status modal -->
                    <div id="unsubscribe_email_list_modal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title"><strong>Unsubscribe Email Notification List</strong></h4>
                                </div>
                                <div class="modal-body stu_data">
                                </div>
                                <div class="modal-footer">
                                    <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<?php if (isset($_GET['tab']) && $_GET['tab'] == "view-call-logs") { ?>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <h2 class="page-header"><strong>Filters</strong></h2>
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
                        <label for="">Select Client</label>
                        <select name="tab" class="form-group select2-field">
                            <option value="">Select</option>
                            <!-- <option value="all-clients">All Clients</option> -->
                            <option value="reocurring">Reocurring</option>
                            <option value="non-reocurring">Non-Reocurring</option>
                            <option value="cold-calls">Cold Calls</option>
                            <option value="view-call-logs">View Call Logs</option>

                        </select>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
        <!-- Trigger the modal with a button -->
        <div class="card full_width table-responsive">
            <div class="card-body">
                <?php if ($coldcalls) : ?>
                    <h3 class="card-title">Cold Calls Logs <small>(<?= $total_rows; ?> results found)</small></h3>
                    <table id="myTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email Id</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Description </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($coldcalls) && count($coldcalls) > 0) : ?>
                                <?php foreach ($coldcalls as $coldcall) : ?>
                                    <tr>
                                        <td><?= $coldcall->name; ?></td>
                                        <td><?= $coldcall->email; ?></td>
                                        <td><?= $coldcall->phone; ?></td>
                                        <td><?= $coldcall->cold_date; ?></td>
                                        <td><?= $coldcall->description; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9">No Record Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>


                <?php else : ?>
                    <h3 class="text-center text-danger">No Cold Call Found</h3>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php  } else { ?>



<?php  }  ?>


<!--Create Email Modal -->
<div id="email_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><strong>Create Client</strong></h4>
            </div>
            <div class="modal-body">

                <form id="create_cold_calls_form" action="<?= admin_url('admin-post.php'); ?>" method="post">

                    <?php wp_nonce_field('create_email'); ?>

                    <!-- <form id="create_email_form" method="post" action="< ?= admin_url('admin-post.php'); ?>"> -->
                    <!-- < ?php wp_nonce_field('create_email'); ?> -->
                    <input type="hidden" name="action" value="create_email">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="form-group">
                        <label for="">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Name</label>
                        <input type="text" name="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Phone No.</label>
                        <input type="text" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="">Type (clients)</label>
                        <select name="status" id="ddlPassport" onchange="ShowHideDiv()" class="form-control select2-field">
                            <option value="">Select</option>
                            <option value="cold_calls" onclick="myFunction()" id="myelement">Cold Calls (not a client)</option>
                            <option value="non_reocurring">Non-reocurring (client)</option>
                            <option value="reocurring">Reocurring (client)</option>
                        </select>
                    </div>

                    <div class="form-group another-element" id="myDIV">
                        <div id="dvPassport" style="display: none">
                            <label for="">Book Appointment</label>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="radio1" name="book_appointment" value="1" checked>
                                <label class="form-check-label" for="radio1"> Booked</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" onclick="myFunction()" id="radio2" name="book_appointment" value="0">
                                <label class="form-check-label" for="radio2"> Not Booked</label>
                            </div>

                        </div>

                        <div class="form-group"></div>
                        <div class="form-group">
                            <!-- Cold-Call Note -->
                            <div class="form-group" id="atextarea">
                                <label for="review_link">Enter the Notes : </label>
                                <textarea class="form-control" rows="4" cols="50" name="note" id="note"></textarea>
                            </div>
                        </div>

                    </div>
                    <hr>

                    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Email</button>
                    <!-- <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> </button> -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!--Download Email CSV Modal -->
<div id="email_csv_download_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><strong>Download Client Database</strong></h4>
            </div>
            <div class="modal-body">
                <form method="post" id="download_emails_csv_form" action="<?= admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="download_emails_csv">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="form-group">
                        <label for="">From Date</label>
                        <input type="date" class="form-control" name="from_date">
                    </div>
                    <div class="form-group">
                        <label for="">To Date</label>
                        <input type="date" class="form-control" name="to_date">
                    </div>
                    <div class="form-group">
                        <label for="">Branch</label>
                        <select name="branch_id" class="form-group select2-field">
                            <option value="">All</option>
                            <?php if (is_array($branches) && count($branches) > 0) : ?>
                                <?php foreach ($branches as $branch) : ?>
                                    <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Type (Clients)</label>
                        <select name="type" class="form-control select2-field">
                            <option value="">Select</option>
                            <option value="all_clients">All (Clients)</option>
                            <option value="non_reocurring">Non-reocurring (Clients)</option>
                            <option value="reocurring">Reocurring (Clients)</option>
                        </select>
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-download"></i></span> Download CSV</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Put client in unsubscribe campaign -->
<div id="banClientFromCampaignModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><strong>Unsubscribe From Email Notification</strong></h4>
            </div>
            <div class="modal-body">

                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="banClientCampaignForm" action="<?= admin_url('admin-post.php') ?>" method="post">

                        <!-- <form id="create_cold_calls_form" action="< ?= admin_url('admin-post.php'); ?>" method="post"> -->
                        <?php wp_nonce_field('unsubscribe_from_satisfaction_email'); ?>

                        <input type="hidden" name="action" value="unsubscribe_from_satisfaction_email">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="client_id">
                        <input type="hidden" name="client_email">

                        <!-- all campaigns  -->
                        <div class="form-group">
                            <label for="">Select Notification Type</label>
                            <select name="email_campaign_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php foreach ($all_campaigns as $campaign) : ?>
                                    <option value="<?php echo $campaign->id; ?>"><?php echo $campaign->campaign_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- SUBMIT BUTTON  -->
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Submit</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Create Call Logs -->
<div id="updateColdCallLogsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><strong>Create Cold Logs</strong></h4>
            </div>
            <div class="modal-body">

                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="updateColdCallLogForm" action="<?= admin_url('admin-post.php') ?>" method="post">

                        <!-- <form id="create_cold_calls_form" action="< ?= admin_url('admin-post.php'); ?>" method="post"> -->
                        <?php wp_nonce_field('create_cold_calls_log'); ?>

                        <input type="hidden" name="action" value="create_cold_calls_log">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="cold_call_id">

                        <!-- Callrail Date  -->
                        <div class="form-group">
                            <label for="branch_name">Date</label>
                            <input type="date" class="form-control" name="cold_date" id="cold_date">
                        </div>

                        <!-- Callrail Discription -->
                        <div class="form-group">
                            <label for="review_link">Description : </label>
                            <textarea class="form-control" rows="4" cols="50" name="description" id="description"></textarea>
                        </div>

                        <!-- SUBMIT BUTTON  -->
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Submit</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
    <script>
        function ShowHideDiv() {
            var ddlPassport = document.getElementById("ddlPassport");
            var dvPassport = document.getElementById("dvPassport");
            dvPassport.style.display = ddlPassport.value == "cold_calls" ? "block" : "none";
        }

        function ShowHideDiv1() {
            var ddlPassport = document.getElementById("ddlPassport1");
            var dvPassport = document.getElementById("dvPassport1");
            dvPassport.style.display = ddlPassport.value == "0" ? "block" : "none";
        }

        // call logs 
        const updateColdCallsLogs = (cold_call_id) => {
            jQuery('#updateColdCallLogForm input[name="cold_call_id"]').val(cold_call_id);
            jQuery('#updateColdCallLogsModal').modal('show');
        }

        const banClientFromCampaign = (client_id,client_email) => {
            jQuery('#banClientFromCampaignModal input[name="client_id"]').val(client_id);
            jQuery('#banClientFromCampaignModal input[name="client_email"]').val(client_email);
            jQuery('#banClientFromCampaignModal').modal('show');
        }

        (function($) {
            $(document).ready(function() {
                $('#create_email_form').validate({
                    rules: {
                        email: {
                            required: true,
                            email: true,
                            remote: {
                                url: "<?= admin_url('admin-ajax.php'); ?>",
                                data: {
                                    action: "check_if_email_exist"
                                },
                                type: "post"
                            }
                        },
                        type: "required"
                    },
                    messages: {
                        email: {
                            remote: "Email already exists"
                        }
                    }
                });

                $('#download_emails_csv_form').validate({
                    rules: {
                        from_date: "required",
                        to_date: "required",
                        type: "required",
                    }
                })

                $('#banClientCampaignForm').validate({
                    rules: {
                        email_campaign_id: "required"
                    }
                })

            });

            // Validation
            $(document).ready(function() {
                $('#updateColdCallStatusForm').validate({
                    rules: {
                        cold_status: "required",
                        note: "required",
                    }
                })
            });

            // Validation
            $(document).ready(function() {
                $('#updateColdCallLogForm').validate({
                    rules: {
                        cold_date: "required",
                        description: "required",
                    }
                })
            });

            // Validation
            $(document).ready(function() {
                $('#create_cold_calls_form').validate({
                    rules: {
                        name: "required",
                        email: "required",
                        phone: "required",
                        address: "required",
                    }
                })
            });


            jQuery('.clients_intrest_spring').on("click", function() {
                enable_btn_on_client_select('spring_choose_clients[]', '.client_spring_notify');
            });

            let clicked = false;
            jQuery(".select_all_clients").on("click", function() {
                jQuery(".clients_intrest_spring").prop("checked", !clicked);
                clicked = !clicked;
                this.innerHTML = clicked ? '<i class="fa fa-close"></i></span> Deselect All' : '<i class="fa fa-check"></i></span> Select All';
                enable_btn_on_client_select('spring_choose_clients[]', '.client_spring_notify');
            });

            // trigger notification
            jQuery(".client_spring_notify").on("click", function() {
                let inp = 'input[name="spring_choose_clients[]"]:checked';
                atLeastOneIsChecked = jQuery(inp);
                selected_clients = new Array();
                if (atLeastOneIsChecked.length > 0) {
                    $(inp).each(function() {
                        if (!$(this).val() == '') selected_clients.push($(this).val());
                    });
                    jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        dataType: "json",
                        data: {
                            "_wpnonce": "<?= wp_create_nonce('client_bulk_spring_notify') ?>",
                            action: 'notify_client_spring_treat',
                            selected_clients
                        },
                        beforeSend: function() {
                            showLoader('Sending notification to clients, please wait...');
                        },
                        success: function(data) {
                            if (data.status === "success") {
                                new swal('Success!', data.message).then(() => {
                                    window.location.reload();
                                })
                            } else {
                                new Swal('Oops!', data.message, 'error');
                            }
                        },
                        error: function() {
                            new Swal('Oops!', 'Something went wrong, please try again later', 'error');
                        }
                    });
                }
            });

            // View Cold Call Log
            $(document).ready(function() {
                $('.view_reocurring_modal').on('click', function() {

                    let cold_call_id = $(this).attr('data-cold-call-id');

                    $.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: {
                            action: "view_cold_call_logs",
                            "_wpnonce": "<?= wp_create_nonce('view_cold_call_logs'); ?>",
                            cold_call_id: cold_call_id
                        },
                        beforeSend: function() {
                            $('#reocurring_modal .modal-body').html('<div class="loader"></div>');
                        },
                        success: function(data) {
                            $('#reocurring_modal .modal-body').html(data);
                        }
                    })

                });
            });

            // View unsubscribe client campaign list
            $(document).ready(function() {
                $('.view_unsubscribe_email_log').on('click', function() {

                    let client_id = $(this).attr('data-client-id');

                    $.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: {
                            action: "view_unsubscribe_campaign_list",
                            "_wpnonce": "<?= wp_create_nonce('view_unsubscribe_campaign_list'); ?>",
                            client_id: client_id
                        },
                        beforeSend: function() {
                            $('#unsubscribe_email_list_modal .modal-body').html('<div class="loader"></div>');
                        },
                        success: function(data) {
                            $('#unsubscribe_email_list_modal .modal-body').html(data);
                        }
                    })

                });
            });

        })(jQuery);
    </script>
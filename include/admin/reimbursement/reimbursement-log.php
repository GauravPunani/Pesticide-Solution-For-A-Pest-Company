<?php
global $wpdb;

$branches = (new Branches)->getAllBranches();
$technicians = (new Technician_details)->get_all_technicians();

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$conditions = [];

$door_to_door = false;

if (isset($_GET['type']) && !empty($_GET['type'])){
    if($_GET['type'] == 'door-to-door-reimbursement'){
        $employees = (new Employee\Employee)->getAllEmployees(['door_to_door_sale']);
        $door_to_door = true;
        $conditions[] = " emp.role_id=4";
    }else{
        $employees = (new Employee\Employee)->getAllEmployees(['office_staff']);
        $door_to_door = true;
        $conditions[] = " emp.role_id=3";
    }
}

if (!current_user_can('other_than_upstate')) {
    $accessible_branches = (new Branches)->partner_accessible_branches(true);
    $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

    $conditions[] = " TD.branch_id IN ($accessible_branches)";
}

$conditions[] = " RP.status='paid'";

if (isset($_GET['branch_id']) && !empty($_GET['branch_id'])) {
    $conditions[] = " TD.branch_id = '{$_GET['branch_id']}' ";
}
if (isset($_GET['technician_id']) && !empty($_GET['technician_id'])) {
    $conditions[] = " TD.id = '{$_GET['technician_id']}' ";
}
if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
    $conditions[] = " emp.id = '{$_GET['employee_id']}' ";
}
if (isset($_GET['date']) && !empty($_GET['date'])) {
    $conditions[] = " DATE(RP.date_requested)='{$_GET['date']}' ";
}

if (count($conditions) > 0) {
    $conditions = (new GamFunctions)->generate_query($conditions);
} else {
    $conditions = "";
}

if (isset($_GET['search'])) {
    if(isset($_GET['type']) && !empty($_GET['type']) && $_GET['type'] == 'door-to-door-reimbursement'){
        $tbl = 'employees';
        $alias = 'emp';
    }else{
        $tbl = 'technician_details';
        $alias = 'TD';
    }
    $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix . $tbl);
    if (!empty($conditions)) {
        $conditions .= " " . (new GamFunctions)->create_search_query_string($whereSearch, $_GET['search'], 'and', $alias);
    } else {
        $conditions = (new GamFunctions)->create_search_query_string($whereSearch, $_GET['search'], 'where', 'RP');
    }
}

$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;

if ($door_to_door) {
    $total_rows = (new TechnicianDepositProof)->getReimbursementTotalRecord([
        'rp' => 'reimbursement_proof RP',
        'emp' => 'employees emp',
        'on' => 'on RP.employee_id=emp.id',
        'conditions' => $conditions,
    ]);

    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $data = (new TechnicianDepositProof)->getReimbursementRecords([
        'col' => 'emp.name as first_name',
        'rp' => 'reimbursement_proof RP',
        'emp' => 'employees emp',
        'on' => 'on RP.employee_id=emp.id',
        'conditions' => $conditions,
        'offset' => $offset,
        'per_page' => $no_of_records_per_page
    ]);
} else {
    $total_rows = (new TechnicianDepositProof)->getReimbursementTotalRecord([
        'rp' => 'reimbursement_proof RP',
        'emp' => 'technician_details TD',
        'on' => 'on RP.technician_id=TD.id',
        'conditions' => $conditions,
    ]);

    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $data = (new TechnicianDepositProof)->getReimbursementRecords([
        'col' => 'TD.first_name,TD.last_name ',
        'rp' => 'reimbursement_proof RP',
        'emp' => 'technician_details TD',
        'on' => 'on RP.technician_id=TD.id',
        'conditions' => $conditions,
        'offset' => $offset,
        'per_page' => $no_of_records_per_page
    ]);
}
?>

<div class="container">
    <div class="row">

        <div class="col-sm-12 col-md-4">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <form id="filtersForm" action="<?= $_SERVER['REQUEST_URI']; ?>">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if (is_array($branches) && count($branches) > 0) : ?>
                                    <?php foreach ($branches as $branch) : ?>
                                        <option value="<?= $branch->id; ?>" <?= (isset($_GET['branch_id']) && $_GET['branch_id'] == $branch->id) ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php if (!$door_to_door) : ?>
                            <div class="form-group">
                                <label for="">Technician</label>
                                <select name="technician_id" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <?php if (is_array($technicians) && count($technicians) > 0) : ?>
                                        <?php foreach ($technicians as $technician) : ?>
                                            <option value="<?= $technician->id; ?>" <?= (isset($_GET['technician_id']) && $_GET['technician_id'] == $technician->id) ? 'selected' : ''; ?>><?= $technician->first_name . " " . $technician->first_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php else : ?>
                            <div class="form-group">
                                <label for="">Sales Person</label>
                                <select name="employee_id" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <?php if (is_array($employees) && count($employees) > 0) : ?>
                                        <?php foreach ($employees as $employee) : ?>
                                            <option value="<?= $employee->id; ?>" <?= (isset($_GET['employee_id']) && $_GET['employee_id'] == $employee->id) ? 'selected' : ''; ?>><?= $employee->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="">Search By Keyword</label>
                            <input type="text" class="form-control" name="search" value="<?= (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Date</label>
                            <input type="date" class="form-control" name="date" value="<?= (isset($_GET['date']) && !empty($_GET['date'])) ? $_GET['date'] : ''; ?>">
                        </div>
                        
                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <caption>Reimbursement History</caption>
                        <thead>
                            <tr>
                                <th>Paid ?</th>
                                <?php if (!$door_to_door) : ?>
                                    <th>Technician Name</th>
                                <?php else : ?>
                                    <th>Sales Person</th>
                                <?php endif; ?>
                                <th>Amount</th>
                                <th>Receipts</th>
                                <th>Proof Of Reimbursement</th>
                                <th>Date Requested</th>
                                <th>Date Reimbursed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($data) && count($data) > 0) : ?>
                                <?php foreach ($data as $key => $val) : ?>
                                    <tr>
                                        <td><input disabled="disabled" data-reimbursement-id="<?= $val->id; ?>" class="reimbursement_checkbox" type="checkbox" checked></td>
                                        <?php if (!$door_to_door) : ?>
                                            <td><?= $val->first_name . " " . $val->last_name; ?></td>
                                        <?php else : ?>
                                            <td><?= $val->first_name; ?></td>
                                        <?php endif; ?>
                                        <td>$<?= $val->amount; ?></td>
                                        <td><a class="btn btn-primary" target="_blank" href="<?= $val->receipts; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <td><button data-docs='<?= $val->proof_of_reimbursement; ?>' data-reimbursement-id="<?= $val->id; ?>" class="btn btn-primary open_docs_modal"><span><i class="fa fa-eye"></i></span> view</button></td>
                                        <td><?= date('d M Y', strtotime($val->date_requested)); ?></td>
                                        <td><?= date('d M Y', strtotime($val->date_reimbursed)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7">No Record Found</td>
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

<div id="docs_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Proof Of Reimbursement</h4>
            </div>
            <div class="modal-body deposit_docs">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    (function($) {
        $(document).ready(function() {

        });
    })(jQuery);
</script>
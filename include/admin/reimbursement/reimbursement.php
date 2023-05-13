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
    $conditions[] = " T.branch_id IN ($accessible_branches)";
}

$conditions[] = " RP.status='not_paid'";

if (isset($_GET['branch']) && !empty($_GET['branch'])) {
    if (!$door_to_door) $conditions[] = " T.branch_id = '{$_GET['branch']}' ";
    $conditions[] = " emp.branch_id = '{$_GET['branch']}' ";
}
if (isset($_GET['technician_id']) && !empty($_GET['technician_id'])) {
    $conditions[] = " RP.technician_id='{$_GET['technician_id']}' ";
}
if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
    $conditions[] = " emp.id = '{$_GET['employee_id']}' ";
}
if (isset($_GET['from_date']) && !empty($_GET['from_date'])) {
    $conditions[] = " DATE(RP.date_requested)>='{$_GET['from_date']}' ";
}
if (isset($_GET['to_date']) && !empty($_GET['to_date'])) {
    $conditions[] = " DATE(RP.date_requested)<='{$_GET['to_date']}' ";
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

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
        <div class="text-center"><?php (new GamFunctions)->getFlashMessage(); ?></div>
        <div class="col-sm-12 col-md-4">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <form id="filtersForm" action="<?= $_SERVER['REQUEST_URI']; ?>">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if (is_array($branches) && count($branches) > 0) : ?>
                                    <?php foreach ($branches as $branch) : ?>
                                        <option value="<?= $branch->id; ?>" <?= (isset($_GET['branch']) && $_GET['branch'] == $branch->id) ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
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
                            <label for="">From Date</label>
                            <input type="date" class="form-control" name="from_date" value="<?= (isset($_GET['from_date']) && !empty($_GET['from_date'])) ? $_GET['from_date'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" class="form-control" name="to_date" value="<?= (isset($_GET['to_date']) && !empty($_GET['to_date'])) ? $_GET['to_date'] : ''; ?>">
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
                        <caption>Pending Reimbursement</caption>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($data) && count($data) > 0) : ?>
                                <?php foreach ($data as $key => $val) : ?>
                                    <tr>
                                        <td><input data-reimbursement-id="<?= $val->id; ?>" class="reimbursement_checkbox" type="checkbox"></td>
                                        <?php if (!$door_to_door) : ?>
                                            <td><?= $val->first_name . " " . $val->last_name; ?></td>
                                        <?php else : ?>
                                            <td><?= $val->first_name; ?></td>
                                        <?php endif; ?>
                                        <td>$<?= $val->amount; ?></td>

                                        <?php if (!empty($val->receipts) && $val->receipts[0] != "[") : ?>
                                            <td><a class="btn btn-primary" target="_blank" href="<?= $val->receipts; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td><button data-docs='<?= $val->receipts; ?>' class="btn btn-primary open_docs_modal"><span><i class="fa fa-eye"></i></span> View</button></td>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($val->proof_of_reimbursement)) : ?>
                                            <td><button data-docs='<?= $val->proof_of_reimbursement; ?>' class="btn btn-primary proof_of_reimbursement"><span><i class="fa fa-eye"></i></span> View</button></td>
                                        <?php else : ?>
                                            <td><button data-docs='<?= $val->proof_of_reimbursement; ?>' data-reimbursement-id="<?= $val->id; ?>" class="btn btn-primary upload_proof_of_reimbursement "><span><i class="fa fa-upload"></i></span> Upload</button></td>
                                        <?php endif; ?>

                                        <td><?= date('d M Y', strtotime($val->date_requested)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6">No Pending Reimbursement</td>
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

<script>
    (function($) {
        $(document).ready(function() {
            $('.proof_of_reimbursement').on('click', function() {
                const proof_data = $(this).attr('data-docs');
                const proof_html = generateDocsHtml(proof_data);
                $('.proof_body').html(proof_html);
                $('#reimbursement_proof_docs').modal('show');
            });
        });
    })(jQuery);
</script>

<!-- Modal -->
<div id="reimbursement_proof_docs" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Proof of Reimbursement</h4>
            </div>
            <div class="modal-body proof_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="docs_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Receipts</h4>
            </div>
            <div class="modal-body deposit_docs">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="proof_of_reimbursement" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Proof of Reimbursement</h4>
            </div>
            <div class="modal-body">
                <form id="proof_of_reimbursement_form" action="<?= admin_url('admin-post.php'); ?>" enctype="multipart/form-data" method="post">
                    <?php wp_nonce_field('upload_proof_of_reimbursement'); ?>
                    <input type="hidden" name="action" value="upload_proof_of_reimbursement">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="reimbursement_id" value="">
                    <div class="form-group">
                        <label for="">Upload Proof of Reimbursement</label>
                        <input type="file" name="docs[]" class="form-control" multiple required>
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
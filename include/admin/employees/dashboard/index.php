<?php

global $wpdb;

$conditions = [];

$conditions[] = " E.application_status = 'verified'";

$all_employees = (new Employee\Employee)->getAllEmployees();

if(!empty($_GET['employee_type'])) $conditions[] = " ET.id = '{$_GET['employee_type']}'";
if(!empty($_GET['employee_id'])) $conditions[] = " E.id = '{$_GET['employee_id']}'";


if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'employees');
    $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'E');
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}employees E

    left join {$wpdb->prefix}employees_types ET
    on E.role_id = ET.id

    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$employees = $wpdb->get_results("
    select E.id, E.name, E.email, ET.name as employee_type
    from {$wpdb->prefix}employees E

    left join {$wpdb->prefix}employees_types ET
    on E.role_id = ET.id

    $conditions
    order by E.role_id asc
    LIMIT $offset, $no_of_records_per_page 
");

$employee_types = (new Employee\Employee)->getEmployeeTypes();


?>

<div class="container">
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form action="">
                        
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                        <div class="form-group">
                            <label for="">Search</label>
                            <input class="form-control" type="text" name="search" placeholder="e.g. name, email etc.." value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Employee Type</label>
                            <select name="employee_type"  class="form-control select2-field">
                                <option value="">All</option>
                                <?php if(is_array($employee_types) && count($employee_types) > 0): ?>
                                    <?php foreach($employee_types as $employee_type): ?>
                                        <option value="<?= $employee_type->id; ?>" <?= (!empty($_GET['employee_type']) && $_GET['employee_type'] == $employee_type->id) ? 'selected' : ''; ?>><?= $employee_type->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">By Employee</label>
                            <select name="employee_id" class="form-control select2-field">
                                <option value="">All</option>
                                <?php if(is_array($all_employees) && count($all_employees) > 0): ?>
                                    <?php foreach($all_employees as $employee): ?>
                                        <option value="<?= $employee->id ?>" <?= (!empty($_GET['employee_id']) && $_GET['employee_id'] == $employee->id) ? 'selected' : ''; ?>><?= $employee->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter</button>

                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Employees Dashboard</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($employees) && count($employees) > 0): ?>
                                <?php foreach($employees as $employee): ?>
                                    <tr>
                                        <td><?= $employee->employee_type; ?></td>
                                        <td><?= $employee->name; ?></td>
                                        <td><?= $employee->email; ?></td>
                                        <td><button onclick="viewEmployeDashboard(<?= $employee->id; ?>)" class="btn btn-primary"><span><i class="fa fa-eye"></i></span> View Dashboard</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="dashboardLoginForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

    <?php wp_nonce_field('admin_dashboard_login'); ?>

    <input type="hidden" name="action" value="admin_dashboard_login">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="employee_id">

</form>

<script>
    function viewEmployeDashboard(employee_id){
        jQuery('#dashboardLoginForm input[name="employee_id"]').val(employee_id);
        jQuery('#dashboardLoginForm').submit();
    }
</script>
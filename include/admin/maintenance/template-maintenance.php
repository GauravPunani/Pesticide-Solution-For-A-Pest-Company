<?php

if(!empty($_GET['contract_id'])) return get_template_part("/include/admin/maintenance/monthly-quarterly-edit");

global $wpdb;
$upload_dir=wp_upload_dir();

$conditions=[];
$conditions[]=" type='$type'";
$active_tab="";

$pageno = isset($_GET['pageno']) ? esc_html($_GET['pageno']) : 1;
$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page;


if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" MC.branch_id IN ($accessible_branches)";
}

if(!empty($_GET['branch_id'])){
    $active_tab = $_GET['branch_id'];
    if($_GET['branch_id']!="all"){
        $conditions[]=" MC.branch_id = '{$_GET['branch_id']}'";
    }
}

if(!empty($_GET['tab'])){
    $active_tab = $_GET['tab'];
    switch ($_GET['tab']) {
        case 'office-sent-contract':
            $conditions[]=" MC.form_status = 'form_filled_by_staff'";
        break;

        case 'client-complete-contract':
            $conditions[]=" MC.form_status='form_completed_by_client'";
        break;

        case 'not-on-calendar':
            $conditions[]=" MC.on_calendar = 0";
        break;
    }
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : ''; 

if(isset($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'maintenance_contract');

    if(!empty($conditions)){
        $conditions.=" ".(new GamFunctions)->create_search_query_string($whereSearch, $_GET['search'], 'and', 'MC');
    }
    else{
        $conditions=(new GamFunctions)->create_search_query_string($whereSearch, $_GET['search'], '', 'MC');
    }
}
$total_rows = $wpdb->get_var("
    select COUNT(*)
    from  {$wpdb->prefix}maintenance_contract MC
    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$result = $wpdb->get_results("
    select * 
    from {$wpdb->prefix}maintenance_contract MC
    $conditions
    order by date DESC LIMIT 
    $offset, $no_of_records_per_page
");

$branches = (new Branches)->getAllBranches();

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <?php (new Navigation)->maintenancePages((string) @$_GET['contract_type']); ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form id="filtersForm">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch_id" class="form-control select2-field">
                                <option value="">All</option>
                                <?php foreach($branches as $branch): ?>
                                    <option value="<?= $branch->id; ?>" <?= (!empty($_GET['branch_id']) && $_GET['branch_id'] == $branch->id) ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Contract Status</label>
                            <select name="tab" class="form-control select2-field">
                                <option value="">Select</option>
                                <option value="office-sent-contract" <?= (!empty($_GET['tab']) && $_GET['tab'] == "office-sent-contract") ? 'selected' : '';  ?>>Office sent contract but client yet to sign</option>
                                <option value="client-complete-contract" <?= (!empty($_GET['tab']) && $_GET['tab'] == "client-complete-contract") ? 'selected' : '';  ?>>Client Complete Contract</option>
                                <option value="not-on-calendar" <?= (!empty($_GET['tab']) && $_GET['tab'] == "not-on-calendar") ? 'selected' : '';  ?>>Not On Calendar</option>
                            </select>
                        </div>

                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>                        

                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                    </form>
                </div>
            </div>

            <?= file_get_contents(get_template_directory()."/template/maintenance/sign-contract-message.html"); ?>

            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header"><?= ucfirst($type); ?> Maintenance Contract Details <small>(<?= $total_rows; ?> Records Found)</small></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone No.</th>
                                <th>Email</th>
                                <th>Cost Per Visit</th>
                                <th>Total Cost</th>
                                <th>Email Status</th>
                                <th>Contract Start Date</th>
                                <th>Contract End Date</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        
                        </thead>
                        <tbody>
                            <?php if( is_array($result) && !empty($result)):?>
                                <?php foreach($result as $key=>$val):?>
                                    <tr>
                                        <td><?= $val->client_name; ?></td>
                                        <td><?= $val->client_address; ?></td>
                                        <td><?= $val->client_phone_no;  ?></td>
                                        <td><?= $val->client_email; ?></td>
                                        <td>$<?= $val->cost_per_month; ?></td>
                                        <td>$<?= $val->total_cost; ?></td>
                                        <td><?= (new GamFunctions)->emailStatusHtml($val); ?></td>                                        
                                        <td><?= date('d M Y',strtotime($val->contract_start_date)); ?></td>
                                        <td><?= date('d M Y',strtotime($val->contract_end_date)); ?></td>
                                        <td><?= date('d M Y',strtotime($val->date)); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <?php if(!empty($val->pdf_path)): ?>
                                                        <li><a data-contract-id="<?= $val->id; ?>" onclick="downloadMaintenanceContract(this)" href="javascript:void(0)"><span><i class="fa fa-download"></i></span> Download Contract</a></li>
                                                    <?php else: ?>
                                                        <li class="disabled"><a href="javascript:void(0)"><span><i class="fa fa-download"></i></span> Download Contract</a></li>
                                                    <?php endif; ?>
                                                    
                                                    <li><a data-contract-id="<?= $val->id; ?>" data-contract-type="<?= $type; ?>" onclick="deleteMaintenancePlan(this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Contract</a></li>

                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&contract_id=<?= $val->id; ?>" role="button"><span><i class="fa fa-edit"></i></span> Edit Contract</a></li>

                                                    <?php if($val->form_status=="form_filled_by_staff"): ?>
                                                        <li><a class="send_contract_for_sign" data-email="<?= $val->client_email; ?>" data-contract-id="<?= $val->id; ?>" data-contract-type='<?= $type; ?>' href="javascript:void(0)"><span><i class="fa fa-envelope"></i></span> Signature Email</a></li>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(!empty($val->pdf_path)): ?>
                                                        <li><a onclick="smsContractLink(<?= $val->id; ?>, '<?= $type; ?>', '<?= $val->client_phone_no; ?>')" href="javascript:void(0)"><span><i class="fa fa-envelope"></i></span> SMS Contract Link</a></li>
                                                    <?php endif; ?>
                                                </ul>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8">No Record found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php get_template_part('/include/admin/maintenance/maintenance-contract-download',null,['action' => 'regular_contract_download']);?>
<?php get_template_part('/include/admin/maintenance/template-email-modal'); ?>
<?php get_template_part('template-parts/maintenance/sms-link-modal'); ?>

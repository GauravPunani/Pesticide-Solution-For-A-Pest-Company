<?php

global $wpdb;

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$whereSearch=[];

if(isset($_GET['search'])){
    $tc_table=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'texas_chemicals');
    $whereSearch=(new GamFunctions)->create_search_query_string($tc_table,$_GET['search'],'where','TC');  

    $tcr_table=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'texas_chemical_report');
    $whereSearch.=(new GamFunctions)->create_search_query_string($tcr_table,$_GET['search'],'and','TCR');  
}
else{
    $whereSearch="";
}

// echo $whereSearch;wp_die();

$total_pages_sql = "
select count(*)
from {$wpdb->prefix}texas_chemicals TC
left join {$wpdb->prefix}texas_chemical_report TCR
on TC.report_id=TCR.id
$whereSearch
";

$total_rows= $wpdb->get_var($total_pages_sql);

$total_pages = ceil($total_rows / $no_of_records_per_page);

$chemical_reports = $wpdb->get_results("
select TC.*,TCR.date,TCR.place_of_application,TCR.applicator_name,TCR.to_application_made,C.name,C.epa_reg_no,C.dosage_rate
from {$wpdb->prefix}texas_chemicals TC
left join {$wpdb->prefix}texas_chemical_report TCR
on TC.report_id=TCR.id
left join {$wpdb->prefix}chemicals C
on TC.product_id=C.id
$whereSearch
order by TCR.date desc
LIMIT $offset, $no_of_records_per_page");

// echo "<pre>"; print_r($chemical_reports);wp_die();
            
?>

<button type="button" class="btn btn-primary openmodal pull-right" data-model-id="annualreport"><span><i class="fa fa-download"></i></span> Download Report</button>

<div class="card">
    <div class="card-body">
        <form action="<?= $_SERVER['REQUEST_URI']; ?>" >
            <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
            <div class="form-group">
                <label for="">Search Records</label>
                <input name="search" placeholder="Enter Name,email etc.." class="form-control" type="text">
            </div>
            
            <button class="btn btn-primary btn_search"><span><i class="fa fa-search"></i></span> Search</button>
        </form>
    </div>
</div>

<?php if(isset($_GET['search'])): ?>
    <p class="alert alert-success"><?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page=chemical-report-texas'); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a> </p>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <table width="100%" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Dosage Rate</th>
                                <th>Epa. reg. no.</th>
                                <th>Plcae of Application</th>
                                <th>Applicator Name</th>
                                <th>To Application Made</th>
                                <th>Application Site</th>
                                <th>Wind Direction</th>
                                <th>Wind Velocity</th>
                                <th>Air Temprature</th>
                                <th>Target Pest</th>
                                <th>Type of Equipment</th>
                                <th>Date</th>
                            </tr>  
                        </thead>
                        <tbody>
                            <tr>
                                <?php if( is_array($chemical_reports) && !empty($chemical_reports)): ?>
                                    <?php foreach($chemical_reports as $key=>$val): ?>
                                        <tr>
                                            <td><?= $val->name; ?></td>
                                            <td><?= $val->dosage_rate; ?></td>
                                            <td><?= $val->epa_reg_no; ?></td>
                                            <td><?= $val->place_of_application; ?></td>
                                            <td><?= $val->applicator_name; ?></td>
                                            <td><?= $val->to_application_made;  ?></td>
                                            <td><?= $val->application_site; ?></td>
                                            <td><?= $val->wind_direction; ?></td>
                                            <td><?= $val->wind_velocity; ?></td>
                                            <td><?= $val->air_temprature; ?></td>
                                            <td><?= $val->target_pest; ?></td>
                                            <td><?= $val->type_of_equipment; ?></td>
                                            <td><?= date('d M Y',strtotime($val->date)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">No Record found</td>
                                    </tr>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="annualreport" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Download Report</h4>
            </div>
            <div class="modal-body">
                <form action="<?= esc_url( admin_url('admin-post.php') ); ?>" method="post" class="">
                    <input type="hidden" name="action" value="texas_csv_report">

                    <div class="form-group">
                        <label for="from date">From Date</label>
                        <input type="date" name="from_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="to date">To Date</label>
                        <input type="date" name="to_date" class="form-control" required>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-download"></i></span> Download</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php

global $wpdb;

$applicators=$wpdb->get_results("select id,first_name,last_name,certification_id from {$wpdb->prefix}technician_details where certification_id<>''");
// echo "<pre>";print_r($applicators);wp_die();

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$whereSearch=[];

if(isset($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'chemicals_newyork');
    $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'where','CRNY');  //genereate where query string
}
else{
    $whereSearch="";
}

// echo $whereSearch;wp_die();

$total_pages_sql = "
select count(*)
from {$wpdb->prefix}chemicals_newyork CRNY
left join {$wpdb->prefix}technician_details T
on CRNY.technician_id=T.id
left join {$wpdb->prefix}chemicals C
on CRNY.chemical_id=C.id
$whereSearch
order by CRNY.date desc
";

$total_rows= $wpdb->get_var($total_pages_sql);

$total_pages = ceil($total_rows / $no_of_records_per_page);

$chemical_reports = $wpdb->get_results("
select CRNY.*,T.first_name,T.last_name,T.certification_id,C.name,C.dosage_rate,C.epa_reg_no
from {$wpdb->prefix}chemicals_newyork CRNY
left join {$wpdb->prefix}technician_details T
on CRNY.technician_id=T.id
left join {$wpdb->prefix}chemicals C
on CRNY.chemical_id=C.id
$whereSearch
order by CRNY.date desc
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
    <p class="alert alert-success"><?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page=chemical-reports-newyork'); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a> </p>
<?php endif; ?>

<div class="table-responsive">
    <table width="100%" class="gamex_table table-striped table-hover">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Dosage Rate</th>
                <th>#Epa</th>
                <th>Product Quantity</th>
                <th>Unit of measurement</th>
                <th>Date</th>
                <th>County Code</th>
                <th>Address</th>
                <th>City</th>
                <th>Zip</th>
                <th>Method Of Application</th>
                <th>Target Organisms</th>
                <th>Place Of Application</th>
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
                            <td><?= $val->product_quantity;  ?></td>
                            <td><?= $val->unit_of_measurement; ?></td>
                            <td><?= $val->date; ?></td>
                            <td><?= $val->county_code; ?></td>
                            <?php $address=json_decode($val->address_of_application);?>
                            <td><?= $address[0]; ?></td>
                            <td><?= $address[1]; ?></td>
                            <td><?= $address[2]; ?></td>
                            <td><?= $val->method_of_application; ?></td>
                            <td><?= $val->target_organisms; ?></td>
                            <td><?= $val->place_of_application; ?></td>
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
                    <input type="hidden" name="action" value="generate_newyork_report">

                    <div class="form-group">
                        <label for="">Applicator Name</label>
                        <select name="applicator" class="form-control select2-field" required>
                            <option value="">Select</option>
                            <?php if(is_array($applicators) && count($applicators)>0): ?>
                                <?php foreach($applicators as $applicator): ?>
                                    <option value="<?= $applicator->certification_id; ?>"><?= $applicator->first_name." ".$applicator->last_name." ".$applicator->certification_id; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="from date">From Date</label>
                        <input type="date" name="from_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="to date">To Date</label>
                        <input type="date" name="to_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="">Report Type</label>
                        <select name="report_type" class="form-control select2-field" required>
                            <option value="">Select</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
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

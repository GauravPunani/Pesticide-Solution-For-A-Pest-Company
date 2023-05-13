<?php
global $wpdb;

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$whereSearch=[];

if(isset($_GET['search'])){
    $crc_columns=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'checmical_report_california');
    $whereSearch=(new GamFunctions)->create_search_query_string($crc_columns,$_GET['search'],'where','CRC');

    $cc_columns=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'chemicals_california');
    $whereSearch.=(new GamFunctions)->create_search_query_string($cc_columns,$_GET['search'],'and','CC');
}
else{
    $whereSearch="";
}

$total_pages_sql = "
select count(*)
from {$wpdb->prefix}checmical_report_california CRC 
left join {$wpdb->prefix}chemicals_california CC
on CRC.id=CC.report_id 
left join {$wpdb->prefix}chemicals C
on CC.product_id=C.id 
$whereSearch 
";

$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);

$result = $wpdb->get_results("
select CRC.client_name,CRC.client_address,CRC.date ,CC.*,C.name,C.dosage_rate,C.epa_reg_no
from {$wpdb->prefix}chemicals_california CC
left join {$wpdb->prefix}checmical_report_california CRC 
on CRC.id=CC.report_id 
left join {$wpdb->prefix}chemicals C
on CC.product_id=C.id 
$whereSearch 
ORDER BY CRC.date DESC 
LIMIT $offset, $no_of_records_per_page");

// echo "<pre>";print_r($result);wp_die();
?>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#report_form"><span><i class="fa fa-download"></i></span> Download Report</button>

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
        <p class="alert alert-success"><?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page=chemical-reports-california'); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a> </p>
    <?php endif; ?>
    
    <div class="card full_width table-responsive">
        <div class="card-body">
            <h3 class="page-header">California Chemical Report</h3>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Client Address</th>
                        <th>Product</th>
                        <th>Dosage Rate</th>
                        <th>#Epa</th>
                        <th>Product Quantity</th>
                        <th>Unit of Measurement</th>
                        <th>Target Organisms</th>
                        <th>Place of Application</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if( is_array($result) && !empty($result)): ?>
                        <?php foreach($result as $key=>$val): ?>
                            <tr>
                                <td><?= $val->client_name; ?></td>
                                <td><?= $val->client_address; ?></td>
                                <td><?= $val->name; ?></td>
                                <td><?= $val->dosage_rate; ?></td>
                                <td><?= $val->epa_reg_no; ?></td>
                                <td><?= $val->product_quantity; ?></td>
                                <td><?= $val->unit_of_measurement; ?></td>
                                <td><?= $val->target_organisms; ?></td>
                                <td><?= $val->place_of_application; ?></td>
                                <td><?= date('d M Y',strtotime($val->date)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No Record found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>        
        </div>
    </div>

<div id="report_form" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Download California Chemical Report</h4>
      </div>
      <div class="modal-body">
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="dwn_form">
            <input type="hidden" name="action" value="generate_california_report">
            <input type="hidden" name="report_year" value="<?= date('Y'); ?>">
            <div class="form-group">
                <label for="">From Date</label>
                <input class="form-control" type="date" name="from_date" required>
            </div>
            <div class="form-group">
                <label for="">To Date</label>
                <input class="form-control" type="date" name="to_date" required>
            </div>
            <div class="form-group">
                <label for="">Report Format</label>
                <select name="report_type" class="form-control select2-field" required>
                    <option value="">Select</option>
                    <option value="pdf">PDF</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
            <button class="btn btn-primary btn_download dwn_anl_report"><span><i class="fa fa-download"></i></span> Download Report</button>
        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

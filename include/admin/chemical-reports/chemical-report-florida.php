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

    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'florida_chemical_report');
    $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'','FCR');  
}
else{
    $whereSearch="";
}

$total_pages_sql = "
select count(*) 
from {$wpdb->prefix}florida_chemical_report FCR 
left join {$wpdb->prefix}technician_details TD
on FCR.technician_id=TD.id
left join {$wpdb->prefix}chemicals C
on FCR.product_id=C.id
$whereSearch
";

$total_rows= $wpdb->get_var($total_pages_sql);

$total_pages = ceil($total_rows / $no_of_records_per_page);

$data=$wpdb->get_results("
select FCR.*,TD.first_name,TD.last_name,C.name,C.dosage_rate,C.epa_reg_no 
from {$wpdb->prefix}florida_chemical_report FCR 
left join {$wpdb->prefix}technician_details TD
on FCR.technician_id=TD.id
left join {$wpdb->prefix}chemicals C
on FCR.product_id=C.id
$whereSearch
order by FCR.date desc
LIMIT $offset, $no_of_records_per_page");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Chemical Report Florida</h3>
                    <form action="<?= $_SERVER['REQUEST_URI']; ?>" class="search_form">
                        <input type="hidden" name="page"  value="<?= $_GET['page']; ?>">
                        <input name="search" type="text"><span><button class="btn btn-primary btn_search"><span><i class="fa fa-search"></i></span> Search</button></span>
                    </form>

                    <?php if(isset($_GET['search'])): ?>
                        <p class="alert alert-success"><?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page='.$_GET['page']); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a> </p>
                    <?php endif; ?>
                    
                    
                    <button data-toggle="modal" data-target="#myModal" class="btn btn-primary"><span><i class="fa fa-download"></i></span> Download Report</button>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician Name</th>
                                <th>Description of Treatment</th>
                                <th>Size of Treatment</th>
                                <th>Product</th>
                                <th>Dosage Rate</th>
                                <th>#Epa</th>
                                <th>Amount of Pesticide</th>
                                <th>Method of Application</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($data) && count($data)>0): ?>
                                <?php foreach($data as $key=>$val): ?>
                                    <tr>
                                        <td><?= $val->first_name." ".$val->last_name ?></td>
                                        <td><?= $val->description_of_treatment; ?></td>
                                        <td><?= $val->size_of_treatment; ?></td>
                                        <td><?= $val->name; ?></td>
                                        <td><?= $val->dosage_rate; ?></td>
                                        <td><?= $val->epa_reg_no; ?></td>
                                        <td><?= $val->amount_of_pesticide; ?></td>
                                        <td><?= $val->method_of_application; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Download Chemical Report Florida</h4>
            </div>
            <div class="modal-body">
                <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="download_chemical_report_florida">
                    <input type="hidden" name="redirect_uri" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="form-group">
                            <label for="">Start Date</label>
                            <input class="form-control" type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                            <label for="">End Date</label>
                            <input class="form-control" type="date" name="end_date" required>
                    </div>
                    <div class="form-group">
                        <label for="">Report Format</label>
                        <select name="report_type" class="form-control select2-field" required>
                            <option value="">Select</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-download"></i></span> Download Report</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
  </div>
</div>
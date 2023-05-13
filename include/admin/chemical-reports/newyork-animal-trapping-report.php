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

    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'ny_animal_trapping_report');
    $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'',$wpdb->prefix.'ny_animal_trapping_report');  //genereate where query string

    // echo $whereSearch;wp_die();
}
else{
    $whereSearch="";
}

$total_pages_sql = "
select count(*) 
from {$wpdb->prefix}ny_animal_trapping_report NYAT
left join {$wpdb->prefix}technician_details TD
on NYAT.technician_id=TD.id 
$whereSearch 
";

$total_rows= $wpdb->get_var($total_pages_sql);

$total_pages = ceil($total_rows / $no_of_records_per_page);

$data=$wpdb->get_results("
select NYAT.*,TD.first_name,TD.last_name 
from {$wpdb->prefix}ny_animal_trapping_report NYAT
left join {$wpdb->prefix}technician_details TD
on NYAT.technician_id=TD.id 
$whereSearch 
LIMIT $offset, $no_of_records_per_page");

// echo '<pre>';print_r($data);wp_die();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Newyork Animal Trapping Report</h3>
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
                                <th>Technician</th>
                                <th>Name & Address</th>
                                <th>Nuissance Species</th>
                                <th>Complaint Type</th>
                                <th>Abatement method</th>
                                <th>Area of complaint</th>
                                <th>Number of traps</th>
                                <th>Species and Number Taken</th>
                                <th>Disposition of animal</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($data) && count($data)>0): ?>
                                <?php foreach($data as $key=>$val): ?>
                                    <tr>
                                        <td><?= $val->first_name." ".$val->last_name ?></td>
                                        <td><?= $val->name_address; ?></td>
                                        <td><?= $val->nuissance_species; ?></td>
                                        <td><?= $val->complaint_type; ?></td>
                                        <td><?= $val->abatement_method; ?></td>
                                        <td><?= $val->area_of_complaint; ?></td>
                                        <td><?= $val->no_of_traps; ?></td>
                                        <td><?= $val->speicies_no_taken; ?></td>
                                        <td><?= $val->desposition_of_animal; ?></td>
                                        <td><?= date('d M Y',strtotime($val->date)); ?></td>
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
                    <input type="hidden" name="action" value="download_ny_animal_trapping_report">
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
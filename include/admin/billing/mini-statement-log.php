<?php

global $wpdb;

$upload_dir=wp_upload_dir();
$conditions=[];

if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id'] != "all"){
    $conditions[]=" I.branch_id='{$_GET['branch_id']}'";
}

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

if(isset($_GET['search'])){
    $search_query=(new GamFunctions)->get_table_coloumn($wpdb->prefix."invoices");
    if(!empty($conditions)){
        $conditions=" ".(new GamFunctions)->create_search_query_string($search_query,trim($_GET['search']),'and','I');
    }
    else{
        $conditions=(new GamFunctions)->create_search_query_string($search_query,trim($_GET['search']),'where','I');
    }
}
else{
    $search_query="";
}


if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$limit =50;
$offset = ($pageno-1) * $limit;



$total_logs_query="
select count(*)
from {$wpdb->prefix}mini_statements MS 
left join {$wpdb->prefix}invoices I
on MS.invoice_id=I.id 
$conditions
";

$final_query="
select MS.*,I.client_name,I.phone_no,I.address,I.email 
from {$wpdb->prefix}mini_statements MS 
left join {$wpdb->prefix}invoices I
on MS.invoice_id=I.id 
$conditions
order by MS.date_created DESC
LIMIT $offset,$limit
";


$total_rows=$wpdb->get_var($total_logs_query);
$logs=$wpdb->get_results($final_query);
$total_pages = ceil($total_rows / $limit);

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3>Search For Records</h3>
                </div>
                <div class="card-body">
                <form class="form-inline" action="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="form-group">
                        <input type="hidden" name="page"  value="<?= $_GET['page']; ?>">
                        <input type="hidden" name="tab"  value="<?= isset($_GET['tab']) ? urlencode($_GET['tab']) : 'all'; ?>">    
                        <input type="text" class="form-control" name="search" id="search_box">
                        <label for="email"><button class="btn btn-primary"><span><i class="fa fa-search"></i> Search</span></button></label>
                    </div>
                </form>    
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php if(isset($_GET['search'])): ?>
                        <p class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page='.$_GET['page'].'&tab='.$_GET['tab']); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
                        </p>
                    <?php endif; ?>
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Phone No.</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Amount</th>
                                <th>Date Sent</th>
                                <th>Mini Statment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($logs) && count($logs)>0): ?>
                                <?php foreach($logs as $key=>$val): ?>
                                    <tr>
                                        <td><?= $val->client_name; ?></td>
                                        <td><?= $val->phone_no; ?></td>
                                        <td><?= $val->address; ?></td>
                                        <td><?= $val->email; ?></td>
                                        <td>$<?= $val->amount; ?></td>
                                        <td><?= date('d M Y',strtotime($val->date_created)); ?></td>
                                        <td><a class="btn btn-primary" href="<?= $upload_dir['baseurl'].$val->pdf_path; ?>" target="_blank"><span><i class="fa fa-eye"></i></span> View Mini Statement</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">No Record Found</td>
                                    </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>

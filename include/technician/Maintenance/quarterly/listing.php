<?php
global $wpdb;

$user=$args['user'];


if(isset($_GET['maintenance-id']) && !empty($_GET['maintenance-id'])){
    get_template_part('/include/technician/Maintenance/quarterly/details');
    return;
}

$whereSearch="";
if(isset($_GET['search'])){
    
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'maintenance_contract');
    $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,trim($_GET['search']),'and');  //genereate where query string

}
else{
    $whereSearch="";
}    



if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page = 10;
$offset = ($pageno-1) * $no_of_records_per_page; 


$total_pages_sql = "select COUNT(*) from  {$wpdb->prefix}maintenance_contract where type='quarterly' and technician_id='{$user->id}' $whereSearch";

$total_rows= $wpdb->get_var($total_pages_sql);

$total_pages = ceil($total_rows / $no_of_records_per_page);

$total_pages=$total_pages < 1 ? 1 : $total_pages;

$result = $wpdb->get_results("select * from {$wpdb->prefix}maintenance_contract where type='quarterly' and technician_id='{$user->id}' $whereSearch  order by date DESC LIMIT $offset, $no_of_records_per_page");

?>

<div class="top_wrapper">
    <h3 class="text-center">Quarterly Maintenance Contracts</h3>

    <div class="row">
        <div class="col-md-3">
            <form action="<?= $_SERVER['REQUEST_URI']; ?>">
                <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                <div class="form-group">
                    <label for="">Search Records</label>
                    <input type="text" name="search" value="<?= @$_GET['search']; ?>" class="form-control">
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
            </form>
        </div>
        <div class="col-md-9">
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <p class="alert alert-success alert-dismissible">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= strtok($_SERVER["REQUEST_URI"], '?'); ?>?view=<?= $_GET['view']; ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">

            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Phone No.</th>
                <th>Email</th>
                <th>Cost Per Month</th>
                <th>Total Cost</th>
                <th>Action</th>
            </tr>

            <tr>    	
                    
                <?php if( is_array($result) && !empty($result)): 
                    $upload_dir=wp_upload_dir();
                    ?>

                    <?php foreach($result as $key=>$val):      ?>
                        <tr>
                            <td><?= $val->client_name; ?></td>
                            <td><?= $val->client_address; ?></td>
                            <td><?= $val->client_phone_no;  ?></td>
                            <td><?= $val->client_email; ?></td>
                            <td><?= $val->cost_per_month; ?></td>
                            <td><?= $val->total_cost; ?></td>
                            <td>
                                <a href="<?= $_SERVER['REQUEST_URI']."&maintenance-id={$val->id}" ?>"><span><i class="fa fa-eye"></i></span> View</a>
                            </td>
                        </tr>
                        
                    <?php endforeach; ?>

                <?php else: ?>
                        <tr>
                            <td colspan="10">No Record found</td>
                        </tr>

                <?php endif; ?>
            </tr>

        </table> 
	</div>

    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>

</div>
    
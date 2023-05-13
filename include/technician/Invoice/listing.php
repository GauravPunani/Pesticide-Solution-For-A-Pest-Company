<?php

$user = $args['user'];

global $wpdb;
if(!empty($_GET['invoice_id'])) return get_template_part('include/technician/Invoice/details', null, compact('user'));

$whereSearch="";

if(!empty($_GET['search'])){

    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'invoices');
    $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,trim($_GET['search']),'and');  //genereate where query string

}
else{
    $whereSearch="";
}

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows= $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}invoices 
    where branch_id='$user->branch_id' 
    and technician_id='$user->id' 
    $whereSearch
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$result = $wpdb->get_results("
    SELECT * 
    FROM {$wpdb->prefix}invoices 
    where branch_id='$user->branch_id' 
    and technician_id='$user->id' 
    $whereSearch 
    order by date desc  LIMIT $offset, $no_of_records_per_page
");

//calculate recorde index by page no
$records_starting_index=(($pageno-1)*$no_of_records_per_page)+1;

?>
<div class="table-responsive">
    <h1 class="text-center">Invoices</h1>
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
                        <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= strtok($_SERVER["REQUEST_URI"], '?'); ?>?view=invoice"><span><i class="fa fa-database"></i></span> Show All Records</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone No.</th>
                <th>Payment Method</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if( is_array($result) && !empty($result)): ?>
            <?php foreach($result as $key=>$val): ?>
            <tr>
                <td><?= $records_starting_index; ?></td>
                <td><?= $val->client_name; ?></td>
                <td><?= $val->email; ?></td>
                <td><?= $val->phone_no; ?></td>
                <td><?= (new GamFunctions)->beautify_string($val->payment_method); ?></td>
                <td><?= $val->date; ?></td>
                <td><?= $val->total_amount; ?></td>
                <td><a href="<?= $_SERVER['REQUEST_URI']; ?>&invoice_id=<?= $val->id; ?>">View Invoice</a></td>
            </tr>
            <?php $records_starting_index++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>

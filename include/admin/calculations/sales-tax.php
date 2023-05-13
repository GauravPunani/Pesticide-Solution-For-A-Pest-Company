<?php
global $wpdb;

$conditions=[];

$payment_methods=$wpdb->get_results("
select * 
from {$wpdb->prefix}payment_methods
");


if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$search_array=[];

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" branch_id IN ($accessible_branches)";
}

if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id']!="all"){
    $conditions[]=" branch_id='{$_GET['branch_id']}'";
}

if(isset($_GET['from_date']) && !empty($_GET['from_date'])){
    $conditions[]=" date>='{$_GET['from_date']}'";
}
if(isset($_GET['to_date']) && !empty($_GET['to_date'])){
    $conditions[]=" date<='{$_GET['to_date']}'";
}
if(isset($_GET['payment_method']) && !empty($_GET['payment_method'])){
    $conditions[]=" payment_method='{$_GET['payment_method']}'";
}

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var("
    SELECT COUNT(*)
    FROM {$wpdb->prefix}invoices
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$invoices=$wpdb->get_results("
    select client_name, address, tax
    from {$wpdb->prefix}invoices
    $conditions
    order by tax DESC LIMIT
    $offset, $no_of_records_per_page
");

$total_sales_tax=$wpdb->get_row("
    select SUM(tax) as total_sales_tax 
    from {$wpdb->prefix}invoices 
    $conditions
");

?>


<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <?php (new Navigation)->calculation_navigation($_GET['page']); ?>
            <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center"><b><span><i class="fa fa-filter"></i></span> Filter By:</b></h4>
                    <form action="<?=  $_SERVER['PHP_SELF']; ?>" method="get">
                        <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
                        <div class="form-group">
                            <label for="">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">Select</option>
                                <?php if(is_array($payment_methods) && count($payment_methods)>0): ?>
                                    <?php foreach($payment_methods as $payment_method): ?>
                                        <option value="<?= $payment_method->slug; ?>" <?= $payment_method->slug==@$_GET['payment_method'] ? 'selected': ''; ?>><?= $payment_method->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" class="form-control" name="from_date" value="<?= @$_GET['from_date']; ?>">    
                        </div>
                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" class="form-control" name="to_date" value="<?= @$_GET['to_date']; ?>">    
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-calculator"></i></span> Calculate tax</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card full_width">
                <div class="card-body">
                    <h4 class="text-center"><b><span><i class="fa fa-database"></i></span> Records</b></h4>
                    <table class="table table-striped">
                        <caption>Total Records : <?= $total_rows; ?></caption>
                        <?php if($total_rows>0): ?>
                            <div class="text-right">
                                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                                    <input type="hidden" name="action" value="download_sales_tax_log">
                                    <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                                    <button class="btn btn-success"><span><i class="fa fa-download"></i></span> Download Sales Tax Log</button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Client Address</th>
                                <th>Sales Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if( is_array($invoices) && !empty($invoices)): ?>
                            <?php foreach($invoices as $invoice): ?>
                                <tr>
                                    <td><?= $invoice->client_name; ?></td>
                                    <td><?= $invoice->address; ?></td>
                                    <td><?= $invoice->tax; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center"><b><span><i class="fa fa-list"></i></span> Summary</b></h4>
                    <table class="table table-striped">
                        <?php if(isset($total_sales_tax->total_sales_tax)): ?>
                            <tr>
                                <th>Total Sales Tax</th>
                                <td>$<?= number_format((float)$total_sales_tax->total_sales_tax, 2, '.', ''); ?></td>
                            </tr>
                        <?php endif;?>
                        <?php if(isset($total_rows)): ?>
                            <tr>
                                <th>Total Invoices</th>
                                <td><?= $total_rows; ?></td>
                            </tr>
                        <?php endif;?>
                        <?php if(isset($_GET['payment_method']) && !empty($_GET['payment_method'])): ?>
                            <tr>
                                <th>Payment Method</th>
                                <td><?= ucwords(str_replace('_',' ',$_GET['payment_method']));  ?></td>
                            </tr>
                        <?php endif;?>
                        <?php if(isset($_GET['from_date']) && !empty($_GET['from_date'])): ?>
                            <tr>
                                <th>From Date</th>
                                <td><?= date('d M Y',strtotime($_GET['from_date']));  ?></td>
                            </tr>
                        <?php endif;?>
                        <?php if(isset($_GET['to_date']) && !empty($_GET['to_date'])): ?>
                            <tr>
                                <th>To Date</th>
                                <td><?= date('d M Y',strtotime($_GET['to_date']))  ?></td>
                            </tr>
                        <?php endif;?>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
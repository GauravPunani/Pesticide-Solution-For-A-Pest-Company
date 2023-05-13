<?php
global $wpdb;

$conditions=[];

$conditions[]=" interested_in_quote<>''";
$conditions[]=" quote_amount<>''";

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" branch_id IN ($accessible_branches)";
}

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$total_pages_sql = "
select count(*)
from {$wpdb->prefix}invoices 
$conditions
";

$no_of_records_per_page = 10;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);


$clients=$wpdb->get_results("
    select date,client_name,address,email,phone_no,interested_in_quote,quote_amount 
    from {$wpdb->prefix}invoices 
    $conditions
    order by date DESC 
    LIMIT $offset, $no_of_records_per_page
");
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Non Interested Maintenance Quotes</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Phone No.</th>
                                <th>Interested in Quote</th>
                                <th>Quote Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($clients) && count($clients)>0): ?>
                                <?php foreach($clients as $client): ?>
                                    <tr>
                                        <td><?= $client->client_name; ?></td>
                                        <td><?= $client->address; ?></td>
                                        <td><?= $client->email; ?></td>
                                        <td><?= $client->phone_no; ?></td>
                                        <td><?= $client->interested_in_quote; ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($client->quote_amount); ?></td>
                                        <td><?= date('d M Y',strtotime($client->date)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No Record Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>
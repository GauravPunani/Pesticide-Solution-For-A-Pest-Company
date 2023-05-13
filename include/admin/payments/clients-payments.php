<?php

global $wpdb;

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page = 10;
$offset = ($pageno-1) * $no_of_records_per_page; 

if(isset($_GET['search'])){
    $whereSearch=(new Aaa_function)->get_table_coloumn($wpdb->prefix.'client_payments');
    $whereSearch=(new Aaa_function)->create_search_query_string($whereSearch,$_GET['search'],'where');
}
else{
    $whereSearch="";
}   

$total_pages_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}client_payments $whereSearch";
$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);
$payments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}client_payments $whereSearch ORDER BY `date` DESC LIMIT $offset, $no_of_records_per_page ");

?>

<div class="top_wrapper">
    <form action="<?= $_SERVER['REQUEST_URI']; ?>" class="search_form">
        <input type="hidden" name="page"  value="<?= $_GET['page']; ?>">
        <input name="search" placeholder="Enter Name,email etc.." type="text"><span><button class="button button-primary">Search</button></span>
    </form>

    <?php if(isset($_GET['search'])): ?>
        <p><?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a href="<?= admin_url('admin.php?page=').$_GET['page']; ?>">Show All Records</a> </p>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="gamex_table table-striped table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th>Payment Id</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone No</th>
                    <th>Address</th>
                    <th>Location</th>
                    <th>Cardholder Name</th>
                    <th>Stripe Ref Id</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count((array)$payments)>0): ?>
                    <?php foreach($payments as $payment): ?>
                        <tr>
                            <td><?= $payment->payment_id; ?></td>
                            <td><?= $payment->name; ?></td>
                            <td><?= $payment->email; ?></td>
                            <td>$<?= $payment->phoneno; ?></td>
                            <td><?= $payment->address; ?></td>
                            <td><?= ucfirst(str_replace('_',' ',$payment->location)); ?></td>
                            <td><?= $payment->card_name; ?></td>
                            <td><?= $payment->stripe_ref_id; ?></td>
                            <td>$<?= $payment->amount; ?></td>
                            <?php if(!empty($payment->date)): ?>
                                <td><?= date('d M Y',strtotime($payment->date)); ?></td>
                            <?php else: ?>
                                <td></td>
                            <?php endif; ?>
                            <td><a target="_blank" href="<?= "https://dashboard.stripe.com/payments/".$payment->stripe_ref_id ?>">View on Stripe</a></td>
                        </tr>
                    <?php endforeach; ?> 
                <?php else: ?>
                    <tr>
                        <td colspan="11">No Record Found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
    </div>  
</div> 
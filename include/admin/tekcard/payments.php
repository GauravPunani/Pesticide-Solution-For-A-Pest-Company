<?php

$conditions = [];

if(!empty($_GET['tab']) && $_GET['tab'] != "all"){
    switch ($_GET['tab']) {
        case 'confirmed-payments':
            $conditions[] = " I.status = 'paid'";
        break;
        case 'pending-confirmation':
            $conditions[] = " (I.status = '' || I.status is null || I.status = 'pending')";
        break;
    }
}

if (!empty($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page;

if(count($conditions) > 0)
    $conditions = (new GamFunctions)->generate_query($conditions);
else
    $conditions = "";


$total_pages_sql = "
    select count(*)
    from {$wpdb->prefix}tekcard_payments TP
    left join {$wpdb->prefix}invoices I
    on TP.invoice_id=I.id
    left join {$wpdb->prefix}technician_details T
    on I.technician_id=T.id
    $conditions
";

$total_rows= $wpdb->get_var($total_pages_sql);

$total_pages = ceil($total_rows / $no_of_records_per_page);

$payments = $wpdb->get_results("
    select TP.*,I.client_name,I.address,I.phone_no,I.status,T.first_name,T.last_name 
    from {$wpdb->prefix}tekcard_payments TP
    left join {$wpdb->prefix}invoices I
    on TP.invoice_id=I.id
    left join {$wpdb->prefix}technician_details T
    on I.technician_id=T.id
    $conditions
    order by TP.created_at desc
    LIMIT $offset, $no_of_records_per_page 
");


?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php (new Navigation)->tekcard_navigation(@$_GET['tab']); ?>
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Tekcard Payments</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone No.</th>
                                <th>Transaction ID</th>
                                <th>Amount Charged</th>
                                <th>Technician</th>
                                <th>Date Charged</th>
                                <th>Confirmation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($payments) && count($payments)>0): ?>
                                <?php foreach($payments as $payment): ?>
                                    <tr>
                                        <td><?= $payment->client_name; ?></td>
                                        <td><?= $payment->address; ?></td>
                                        <td><?= $payment->phone_no; ?></td>
                                        <td><?= $payment->transaction_id; ?></td>
                                        <td><?= (new GamFunctions)->beautify_amount_field($payment->amount); ?></td>
                                        <td><?= $payment->first_name." ".$payment->last_name; ?></td>
                                        <td><?= date('d M Y',strtotime($payment->created_at)); ?></td>
                                        <td><?= $payment->status=="paid" ? 'Confirmed' : 'Pending' ; ?></td>
                                        <td><a target="_blank" class="btn btn-primary" href="<?= admin_url('admin.php?page=invoice'); ?>&invoice_id=<?= $payment->invoice_id; ?>"><span><i class="fa fa-eye"></i></span> View Invoice </a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>
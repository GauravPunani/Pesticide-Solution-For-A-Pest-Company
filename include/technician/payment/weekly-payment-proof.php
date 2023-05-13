<?php

use Mpdf\Tag\Em;

if (!isset($args['user'])) return;

$technician_id = $args['user']->id;
$employee_id = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);

if (!$employee_id) return;

global $wpdb;

$conditions = [];

$conditions[] = " P.payment_status = 'paid'";

$conditions[] = " P.employee_id = $employee_id";

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}payments P
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$employees_payments = $wpdb->get_results("
    select *
    from {$wpdb->prefix}payments P
    $conditions
    order by P.created_at Desc
    LIMIT $offset, $no_of_records_per_page
");
?>
<div class="table-responsive">
    <div class="row">
        <h3 class="text-center">Weekly Payment Proofs</h3>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Week</th>
                <th>Amount Paid</th>
                <th>Payment Note</th>
                <th>Payment Proof</th>
            </tr>
        </thead>
        <tbody>
            <?php if (is_array($employees_payments) && count($employees_payments) > 0) : ?>
                <?php foreach ($employees_payments as $employee_payment) : ?>
                    <tr>
                        <?php
                        $week_start_date = date('d M Y', strtotime('this monday', strtotime($employee_payment->week)));
                        $week_end_date = date(('d M Y'), strtotime('this sunday', strtotime($employee_payment->week)));
                        ?>
                        <td><?= $week_start_date . " to " . $week_end_date; ?></td>
                        <td><?= (new GamFunctions)->beautify_amount_field($employee_payment->amount_paid); ?></td>
                        <td><?= $employee_payment->payment_description; ?></td>
                        <?php 
                            if(!empty($employee_payment->proof_docs)){
                                $proof_doc = json_decode($employee_payment->proof_docs)[0];
                            }
                        ?>
                        <td><a target="_blank" href="<?= @$proof_doc->file_url;?>"><button class="btn btn-primary"><span><i class="fa fa-eye"></i></span> View</button></a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">No Payment Proof Found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>
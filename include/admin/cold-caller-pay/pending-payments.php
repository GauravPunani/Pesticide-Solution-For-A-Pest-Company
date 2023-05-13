<?php

global $wpdb;


$conditions=[];

$conditions[]=" CCP.payment_status='pending'";

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$total_pages_sql = "
    select count(*) as total_rows 
    from {$wpdb->prefix}cold_caller_payments CCP
    left join {$wpdb->prefix}cold_callers CC
    on CCP.cold_caller_id=CC.id
    $conditions
";

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);

$cold_caller_payments=$wpdb->get_results("
    select CCP.*,CC.name 
    from {$wpdb->prefix}cold_caller_payments CCP
    left join {$wpdb->prefix}cold_callers CC
    on CCP.cold_caller_id=CC.id
    $conditions
    order by date_created Desc 
    LIMIT $offset, $no_of_records_per_page
");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Pending Payments</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Cold Caller</th>
                                <th>Calulcated Commission</th>
                                <th>Week</th>
                                <th>Date Created</th>
                                <th>Payment Type</th>
                                <th>Total Hours</th>
                                <th>Pay Per Hour</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($cold_caller_payments) && count($cold_caller_payments)>0): ?>
                                <?php foreach($cold_caller_payments as $key=>$cc_payment): ?>
                                    <tr>
                                        <td><?= $cc_payment->name; ?></td>
                                        <td><span class="calcualted_commission_<?= $key; ?>"><?= (new GamFunctions)->beautify_amount_field($cc_payment->calculated_commission); ?></span></td>
                                        <?php 
                                            $week_start_date=date('d M Y',strtotime('this monday',strtotime($cc_payment->week)));
                                            $week_end_date=date(('d M Y'),strtotime('this sunday',strtotime($cc_payment->week)));
                                        ?>
                                        <td><?= $week_start_date." to ".$week_end_date; ?></td>
                                        <td><?= date('d M Y',strtotime($cc_payment->date_created)); ?></td>
                                        <td>
                                            <?= (new GamFunctions)->beautify_string($cc_payment->payment_type); ?>
                                        </td>
                                        <td>
                                            <?= !empty($cc_payment->total_hours) ?  $cc_payment->total_hours : 'N/A'; ?>
                                        </td>
                                        <td>
                                            <?= !empty($cc_payment->pay_per_hour) ?  "$".$cc_payment->pay_per_hour : 'N/A'; ?>
                                        </td>

                                        <td>
                                        <div class="btn-group" role="group" aria-label="Basic example">

                                            <button type="button" data-payment-id="<?= $cc_payment->id; ?>" data-week="<?= $cc_payment->week; ?>" data-cold-caller-id="<?= $cc_payment->cold_caller_id; ?>" data-cc-payment="<?= $cc_payment->calculated_commission; ?>" class="btn btn-primary upoad-payment-proof btn-sm"><span><i class="fa fa-upload"></i></span> Payment Proof</button>

                                            <button type="button" data-commission="<?= $cc_payment->calculated_commission; ?>" data-table-id="<?= $cc_payment->id; ?>" data-row-id="<?= $key; ?>" data-week="<?= $cc_payment->week; ?>" data-cold-caller-id="<?= $cc_payment->cold_caller_id; ?>" class="btn btn-info refresh_commission btn-sm"><span><i class="fa fa-refresh"></i> Refresh</span></button>
                                        </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No Pending Payment Found</td>
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

<!-- PROOF OF PAYMENT MODAL  -->
<?php get_template_part('/include/admin/cold-caller-pay/payment-proof-modal'); ?>

<script>
    (function($){
        $(document).ready(function(){

            // onclick to upload payment proof
            $(document).on('click','.upoad-payment-proof',function(){
                let payment_id=$(this).attr('data-payment-id');
                let cc_payble_amount=$(this).attr('data-cc-payment');
                let cold_caller_id=$(this).attr('data-cold-caller-id');
                let week=$(this).attr('data-week');

                $("#payble_amount").html(`$${cc_payble_amount}`);
                $("input[name='payment_id']").val(payment_id);
                $("input[name='payble_amount']").val(cc_payble_amount);
                $('input[name="cold_caller_id"]').val(cold_caller_id);
                $('input[name="week"]').val(week);

                $('#proof-of-payment').modal('show');
            });

            // onclick to refres commission 
            $(document).on('click','.refresh_commission',function(){
                const week=$(this).attr('data-week');
                const cold_caller_id=$(this).attr('data-cold-caller-id');
                const row_id=$(this).attr('data-row-id');
                const commission=$(this).attr('data-commission');

                $.ajax({
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    type:"post",
                    data:{
                        action:"cc_get_refreshed_commission",
                        week:week,
                        cold_caller_id:cold_caller_id,
						"_wpnonce": "<?= wp_create_nonce('cc_get_refreshed_commission'); ?>"
                    },
                    dataType:"json",
                    beforeSend:function(){
                        $(`.calcualted_commission_${row_id}`).html('<div class="loader"></div>');
                    },
                    success:function(data){
                        if(data.status=="success"){
                            $(`.calcualted_commission_${row_id}`).html(data.data.commission);
                        }
                        else{
                            $(`.calcualted_commission_${row_id}`).html(commission);
                            alert(data.message);
                        }
                    }
                })
            })

        });
    })(jQuery);
</script>
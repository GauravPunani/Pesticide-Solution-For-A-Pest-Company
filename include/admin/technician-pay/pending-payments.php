<?php

global $wpdb;

if(isset($_GET['view-calculation'])){
    get_template_part('/include/admin/technician-pay/view-calculation');
    return;
}

$max_week_no = date('W') - 1;

$week = (isset($_GET['week']) && !empty($_GET['week'])) ? $_GET['week'] : date('Y-\W'.$max_week_no);
$eligibility_status = isset($_GET['eligibility_status']) ? $_GET['eligibility_status'] : 'eligible';

$week_monday = date('d M Y',strtotime('this monday',strtotime($week)));
$week_sunday = date('d M Y',strtotime('this sunday',strtotime($week)));

$conditions = [];
$conditions[] = " P.payment_status = 'pending' and P.role = 'technician'";
$conditions[] = " P.week = '$week'";

if(!empty($eligibility_status)){
    if($eligibility_status == "eligible") $conditions[] = " P.is_eligible = 1";
    if($eligibility_status == "not_eligible") $conditions[] = " P.is_eligible = 0";
}

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" T.branch_id IN ($accessible_branches)";
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var("
    select count(*) as total_rows 
    from {$wpdb->prefix}payments P
    left join {$wpdb->prefix}technician_details T
    on P.user_id=T.id
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$tech_payments = $wpdb->get_results("
    select P.*,T.first_name,T.last_name
    from {$wpdb->prefix}payments P
    left join {$wpdb->prefix}technician_details T
    on P.user_id=T.id
    $conditions
    order by P.created_at Desc
    LIMIT $offset, $no_of_records_per_page
");



?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>            
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header"><span><i class="fa fa-filter"></i></span> Filters</h3>
                    <form action="">

                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="week">Select Week</label>
                            <input class="form-control" value="<?= $week; ?>" max="<?= date("Y-\W".$max_week_no); ?>" type="week" name="week" id="week">
                        </div>

                        <div class="form-group">
                            <label for="eligibility_status">Pay Eligibility Status</label>
                            <select name="eligibility_status" id="eligibility_status" class="form-control select2-field">
                                <option value="">All</option>
                                <option value="eligible" <?= $eligibility_status == "eligible" ? 'selected': ''; ?>>Eligible</option>
                                <option value="not_eligible" <?= $eligibility_status == "not_eligible" ? 'selected': ''; ?>>Not Eligible</option>
                            </select>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search Pending Payments</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Pending Payments</h3>

                    <p><b>Week:</b> From <?= $week_monday; ?> To <?= $week_sunday; ?></p>

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Calulcated Commission</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($tech_payments) && count($tech_payments)>0): ?>
                                <?php foreach($tech_payments as $key=>$tech_payment): ?>
                                    <tr>
                                        <td><?= $tech_payment->first_name." ".$tech_payment->last_name; ?></td>
                                        <td><span class="calcualted_commission_<?= $key; ?>"><?= (new GamFunctions)->beautify_amount_field($tech_payment->calculated_commission); ?></span></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a data-payment-id="<?= $tech_payment->id; ?>" data-week="<?= $tech_payment->week; ?>" data-technician-id="<?= $tech_payment->user_id; ?>" data-tech-payment="<?= $tech_payment->calculated_commission; ?>" class="upoad-payment-proof" href="javascript:void(0)"><span><i class="fa fa-upload"></i></span> Upload Payment Proof</a></li>

                                                    <li><a href="<?= $_SERVER['REQUEST_URI']."&week=$tech_payment->week&user_id=$tech_payment->user_id&view-calculation=true" ?>">
                                                    <span><i class="fa fa-eye"></i></span> View Calculation
                                                    </a></li>

                                                    <li><a data-table-id="<?= $tech_payment->id; ?>" data-row-id="<?= $key; ?>" data-week="<?= $tech_payment->week; ?>" data-technician-id="<?= $tech_payment->user_id; ?>" class="refresh_commission" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Refresh Commission</a></li>

                                                    <?php if(!$tech_payment->is_eligible): ?>
                                                        <li><a onclick="showEneligibilityReasons(<?= $tech_payment->user_id; ?>, '<?= $tech_payment->role; ?>', '<?= $tech_payment->week; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> Show Eneligibility Reasons</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No pending payment record found</td>
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
<?php get_template_part('/include/admin/technician-pay/payment-proof-modal'); ?>

<!-- ENILIGIBILITY REASONS MODAL -->
<div id="eniligibilityReasonsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Eniligibility Reasons</h4>
            </div>
            <div class="modal-body eniligibilityReasons"></div>
        </div>
    </div>
</div>


<script>
    let last_user_id;

    (function($){
        $(document).ready(function(){

            $(document).on('click','.upoad-payment-proof',function(){
                let payment_id=$(this).attr('data-payment-id');
                let tech_payble_amount=$(this).attr('data-tech-payment');
                let tech_id=$(this).attr('data-technician-id');
                let week=$(this).attr('data-week');

                $("#payble_amount").html(`$${tech_payble_amount}`);
                $("input[name='payment_id']").val(payment_id);
                $("input[name='payble_amount']").val(tech_payble_amount);
                $('input[name="user_id"]').val(tech_id);
                $('input[name="week"]').val(week);

                $('#proof-of-payment').modal('show');
            });

            $(document).on('click','.refresh_commission',function(){
                let week=$(this).attr('data-week');
                let user_id=$(this).attr('data-technician-id');
                let row_id=$(this).attr('data-row-id');
                let table_id=$(this).attr('data-table-id');

                $.ajax({
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    type:"post",
                    data:{
                        action:"refresh_tech_pay_calculation",
                        week:week,
                        user_id:user_id,
                        table_id:table_id,
						"_wpnonce": "<?= wp_create_nonce('refresh_calculation'); ?>"
                    },
                    dataType:"json",
                    beforeSend:function(){
                        $(`.calcualted_commission_${row_id}`).html('<div class="loader"></div>');
                    },
                    success:function(data){
                        if(data.status=="success"){
                            $(`.calcualted_commission_${row_id}`).html(data.data.week_final_commission);
                        }
                        else{
                            alert(data.message);
                        }
                    }
                })
            })

        });
    })(jQuery);


    function showEneligibilityReasons(user_id, role, week, ref){

        if(last_user_id !== undefined && last_user_id === user_id){
            jQuery('#eniligibilityReasonsModal').modal('show');
            return;
        }

        last_user_id = user_id;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "get_eniligibility_reasons",
                user_id,
                role,
                week
            },
            beforeSend: function(){
                jQuery('.eniligibilityReasons').html(`<div class="loader"></div>`);
                jQuery('#eniligibilityReasonsModal').modal('show');
            },
            success: function(data){

                if(data.status == "success"){
                    jQuery('.eniligibilityReasons').html(data.data.message);
                }
                else{
                    jQuery('.eniligibilityReasons').html(data.message);
                }

                
            }
        })


    }

</script>
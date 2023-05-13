<?php

global $wpdb;

$conditions = [];

$max_week_no = date('W') - 1;
$active_role = "technician";

if(isset($_GET['active_role']) && !empty($_GET['active_role'])){
    $active_role = $_GET['active_role'];
}

$conditions[] = " ER.slug = '$active_role'";

$week = (isset($_GET['week']) && !empty($_GET['week'])) ? $_GET['week'] : date('Y-\W'.$max_week_no);
$eligibility_status = isset($_GET['eligibility_status']) ? $_GET['eligibility_status'] : 'eligible';

list($week_start, $week_end) = (new GamFunctions)->weekRange($week);


$conditions[] = " P.payment_status = 'pending'";
$conditions[] = " P.week = '$week'";

if(!empty($eligibility_status)){
    if($eligibility_status == "eligible") $conditions[] = " P.is_eligible = 1";
    if($eligibility_status == "not_eligible") $conditions[] = " P.is_eligible = 0";
}

$conditions[] = " E.application_status='verified'";

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page;

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}payments P

    left join {$wpdb->prefix}employees E
    on P.employee_id = E.id

    left join {$wpdb->prefix}employees_types ER
    on ER.id = E.role_id

    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$employee_payments = $wpdb->get_results("
    select P.*, E.name
    from {$wpdb->prefix}payments P
    
    left join {$wpdb->prefix}employees E
    on P.employee_id = E.id

    left join {$wpdb->prefix}employees_types ER
    on ER.id = E.role_id

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

        <div class="col-sm-12">
            <?php (new Navigation)->employeePaymentNavigation(); ?>            
        </div>

        <div class="col-sm-12">
            <?php (new Navigation)->employeeNavigation(@$_GET['active_role']); ?>            
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

                    <p><b>Week:</b> From <?= $week_start; ?> To <?= $week_end; ?></p>

                    <button onclick="refreshEligibility('<?= $week; ?>', this)" class="btn btn-default"><span><i class="fa fa-refresh"></i></span> Refresh Eligibility</button>

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Calulcated Commission</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($employee_payments) && count($employee_payments)>0): ?>
                                <?php foreach($employee_payments as $key => $employee_payment): ?>
                                    <tr>
                                        <td><?= $employee_payment->name; ?></td>
                                        <td><span class="calcualted_commission_<?= $key; ?>"><?= (new GamFunctions)->beautify_amount_field($employee_payment->calculated_commission); ?></span></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a onclick='uploadPaymentProof(`<?= json_encode($employee_payment);?>`)' href="javascript:void(0)"><span><i class="fa fa-upload"></i></span> Upload Payment Proof</a></li>

                                                    <li><a onclick="refreshCommission('<?= $employee_payment->employee_id; ?>', '<?= $employee_payment->week; ?>', <?= $key; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Refresh Commission</a></li>

                                                    <li><a onclick="viewPaymentCalculation('<?= $employee_payment->employee_id; ?>', '<?= $employee_payment->week; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-calculator"></i></span> View Calculation</a></li>

                                                    <?php if(!$employee_payment->is_eligible): ?>
                                                        <li><a onclick="showEneligibilityReasons(<?= $employee_payment->employee_id; ?>, '<?= $employee_payment->week; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> Show Eneligibility Reasons</a></li>
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
<div id="uploadPaymentProofmodal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Proof Of Payment</h4>
            </div>

            <div class="modal-body">
                    <form id="uploadPaymentProofForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

                        <?php wp_nonce_field('upload_proof_of_payment'); ?>
                        <input type="hidden" name="action" value="upload_proof_of_payment">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <input type="hidden" name="payment_id" value="">

                        <p>Payble Amount : <span id="payble_amount"></span></p>

                        <div class="form-group">
                            <label for="">Amount Paid</label>
                            <input type="text" class="form-control" name="amount_paid" required>
                        </div>

                        <div class="form-group">
                            <label for="">Payment Description (optional)</label>
                            <textarea name="payment_description" cols="30" rows="5" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Payment Proof</label>
                            <input type="file" name="payment_proof[]" class="form-control" required multiple/>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Payment Proof</button>
                    </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

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

<!-- VIEW PAYMENT CALCULATION MODAL -->
<div id="viewPaymentCalculationModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Payment Calculation</h4>
            </div>
            <div class="modal-body viewPaymentCalculation"></div>
        </div>
    </div>
</div>


<script>
    let last_employee_id;

    function viewPaymentCalculation(employee_id, week, ref){

        if(last_employee_id !== undefined && last_employee_id === employee_id){
            jQuery('#viewPaymentCalculationModal').modal('show');
            return;
        }

        last_employee_id = employee_id;        

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action: "show_payment_calculation",
                employee_id,
                week
            },
            dataType: "json",
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);                
                jQuery('.viewPaymentCalculation').html(`<div class="loader"></div>`);
                jQuery('#viewPaymentCalculationModal').modal('show');
            },
            success: function(data){
                jQuery('.viewPaymentCalculation').html(data.message);
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
            }
        })
    }

    function refreshEligibility(week, ref){
        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action: "refresh_payment_eligibility",
                week
            },
            dataType: "json",
            beforeSend: function(){
                jQuery(ref).attr('disabled', true);
            },
            success: function (data){
                alert(data.message);

                if(data.status == "success"){
                    window.location.reload();
                }
                else{
                    jQuery(ref).attr('disabled', false);
                }
            }
        })
    }

    function refreshCommission(employee_id, week, key, ref){

        const old_commission = jQuery(`.calcualted_commission_${key}`).html();

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action: "refresh_employee_commission",
                employee_id,
                week
            },
            dataType: "json",
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                jQuery(`.calcualted_commission_${key}`).html(`<div class="loader"></div>`);
            },
            success: function (data){

                if(data.status == "error"){
                    alert(data.message);
                    jQuery(`.calcualted_commission_${key}`).html(old_commission);
                }
                else{
                    jQuery(`.calcualted_commission_${key}`).html(data.data.commission);
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                }
            }
        })
    }

    function uploadPaymentProof(json_data){

        try {
            payment_data = jQuery.parseJSON(json_data);
            console.log('payment_data', payment_data);

            jQuery('input[name="payment_id"]').val(payment_data.id);

            jQuery('#payble_amount').html('$' + payment_data.calculated_commission);

            jQuery('input[name="amount_paid"]').val(payment_data.calculated_commission);

            jQuery('#uploadPaymentProofmodal').appendTo('body').modal('show');            

        } catch (error) {
            console.log('in error');
        }

    }

    (function($){
        $(document).ready(function(){

            $('#uploadPaymentProofForm').validate({
                rules: {
                    amount_paid: "required",
                    "payment_proof[]": "required",
                }
            });

            $(document).on('click','.office_sold',function(){

                let ref = $(this);
                let office_sold = false;
                let type = $(this).attr('data-type');
                let id = $(this).attr('data-id');

                if($(this).prop('checked') === true){
                    office_sold = true;
                }

                // call ajax to update invoice credit field
                $.ajax({
                    type:'post',
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:{
                        office_sold,
                        id,
                        type,
                        action:"update_credit",
						"_wpnonce": "<?= wp_create_nonce('update_credit'); ?>"
                    },
                    dataType:"json",
                    success:function(data){
                        if(data.status === "error"){
                            alert('Something went wrong while update office sold option');
                            ref.prop('checked',false);
                        }
                    }
                })
            });            
        });
    })(jQuery);


    function showEneligibilityReasons(employee_id, week, ref){

        if(last_employee_id !== undefined && last_employee_id === employee_id){
            jQuery('#eniligibilityReasonsModal').modal('show');
            return;
        }

        last_employee_id = employee_id;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "get_eniligibility_reasons",
                employee_id,
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
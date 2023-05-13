<?php

$conditions = [];
$conditions[] = " E.status = 1";

if(isset($_GET['active_role']) && !empty($_GET['active_role'])){
    $conditions[] = " ER.slug = '{$_GET['active_role']}'";
}
else{
    $conditions[] = " ER.slug = 'technician'";
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$employees = $wpdb->get_results("
    select E.*, PS.payment_structure, PS.updated_at
    from {$wpdb->prefix}employees E
    left join {$wpdb->prefix}employees_types ER
    on E.role_id = ER.id
    left join {$wpdb->prefix}payment_structure PS
    on E.id = PS.employee_id
    $conditions
");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php (new Navigation)->employeePaymentNavigation(); ?>            
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>

                    <h3 class="page-header">Payment Structure</h3>                    

                    <?php (new Navigation)->employeeNavigation(@$_GET['active_role']);
                    ?>
                    <br>
                    
                    <p><?= count($employees) ?> employees found</p>

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Last Updated</th>
                                <th>Payment Structure</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($employees) && count($employees) > 0): ?>
                                <?php foreach($employees as $employe): ?>
                                    <tr>
                                        <td><?= $employe->name; ?></td>
                                        <td><?= !empty($employe->updated_at) ? date('d M y h:i A', strtotime($employe->updated_at)) : '-' ; ?></td>
                                        <td>
                                            <?php if(!empty($employe->payment_structure)): ?>
                                                <button onclick='showPaymentStructure( <?= $employe->id; ?>, `<?= $employe->payment_structure; ?>`)' class="btn btn-primary"><span><i class="fa fa-eye"></i> Show</span></button>
                                            <?php else: ?>
                                                <button onclick="showCreatePaymentStructureModal(<?= $employe->id; ?>)" class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="createPaymentStructureModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create Payment Structure</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="paymentStructureForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    
                    <?php wp_nonce_field('create_payment_structure'); ?>
                    <input type="hidden" name="action" value="create_payment_structure">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="employee_id">

                    <div class="form-group">
                        <label for="payment_type">Select Payment Type</label>
                        <select onchange="changePaymentType(this)" name="payment_structure[payment_type]" id="payment_type" class="form-control select2-field">
                            <option value="">Select</option>
                            <option value="percentage_of_route">Percentage (%) of route</option>
                            <option value="x_amount_per_appointement">X Amount Per Appointement</option>
                            <option value="fixed_weekly_salary">Fixed Weekly Salary</option>
                            <option value="x_per_hour">X Per Hour</option>
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="percentage_of_route payment_values hidden">

                        <div class="form-group">
                            <label for="">Enter Percentage of route <span><i><small>(Don't add % symbol)</small></i></span></label>
                            <input placeholder="e.g.20%" value="20" type="text" class="form-control numberonly" name="payment_structure[percentage]">
                        </div>
                        
                        <div class="form-group">
                            <label for="">Amount Per Monthly Maintenance</label>
                            <input   placeholder="e.g. $25" type="text" value="25" class="form-control numberonly" name="payment_structure[monthly_maintenance]">
                        </div>

                        <div class="form-group">
                            <label for="">Amount Per Quarterly Maintenance</label>
                            <input placeholder="e.g. $25" type="text" value="25" class="form-control numberonly" name="payment_structure[quarterly_maintenance]">
                        </div>

                        <div class="form-group">
                            <label for="">Amount Per Special Maintenance</label>
                            <input placeholder="e.g. $25" type="text" value="25" class="form-control numberonly" name="payment_structure[special_maintenance]">
                        </div>

                        <div class="form-group">
                            <label for="">Amount Per Commercial Maintenance</label>
                            <input  placeholder="e.g. $25" type="text" value="25" class="form-control numberonly" name="payment_structure[commercial_maintenance]">
                        </div>

                    </div>

                    <div class="x_amount_per_appointement payment_values hidden">

                        <div class="form-group">
                            <label for="">Enter Amount Per Appointement</label>
                            <input placeholder="e.g. $10" type="text" class="form-control numberonly" name="payment_structure[amount_per_appointement]">
                        </div>
                        
                        <div class="form-group">
                            <label for="">Amount Per Monthly Maintenance</label>
                            <input placeholder="e.g. $25" type="text" value="25" class="form-control numberonly" name="payment_structure[monthly_maintenance]">
                        </div>

                        <div class="form-group">
                            <label for="">Amount Per Quarterly Maintenance</label>
                            <input placeholder="e.g. $25" type="text" value="25" class="form-control numberonly" name="payment_structure[quarterly_maintenance]">
                        </div>

                        <div class="form-group">
                            <label for="">Amount Per Special Maintenance</label>
                            <input placeholder="e.g. $25" type="text" value="25" class="form-control numberonly" name="payment_structure[special_maintenance]">
                        </div>                            


                    </div>

                    <div class="fixed_weekly_salary payment_values hidden">
                        <div class="form-group">
                            <label for="">Enter Fixed Salary</label>
                            <input type="text" class="form-control numberonly" name="payment_structure[fixed_salary]">
                        </div>
                    </div>

                    <div class="x_per_hour payment_values hidden">
                        <p><i>Work hours will be calculated by the system</i></p>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Payment Structure</button>                    
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#paymentStructureForm').validate({
                rules: {
                    "payment_structure[payment_type]": "required",
                    "payment_structure[percentage]": "required",
                    "payment_structure[monthly_maintenance]": "required",
                    "payment_structure[quarterly_maintenance]": "required",
                    "payment_structure[special_maintenance]": "required",
                    "payment_structure[commercial_maintenance]": "required",
                    "payment_structure[amount_per_appointement]": "required",
                    "payment_structure[fixed_salary]": "required",
                }
            })
        })
    })(jQuery);

    function changePaymentType(ref){
        const payment_type = jQuery(ref).val();
        console.log('payment_type');

        jQuery('.payment_values').addClass('hidden');
        jQuery(".payment_values :input").attr("disabled", true);

        jQuery(`.${payment_type}`).removeClass('hidden');
        jQuery(`.${payment_type} :input`).attr('disabled', false);

    }

    function showCreatePaymentStructureModal(employe_id){

        jQuery('input[name="employee_id"]').val(employe_id);
        jQuery('#createPaymentStructureModal').appendTo("body").modal('show');
    }

    function showPaymentStructure(employe_id, json_data){

        try {
            const payment_structure = JSON.parse(json_data);
            

            jQuery('.payment_values').addClass('hidden');
            jQuery(".payment_values :input").attr("disabled", true);

            jQuery(`.${payment_structure.payment_type}`).removeClass('hidden');
            jQuery(`.${payment_structure.payment_type} :input`).attr('disabled', false);

            jQuery('select[name="payment_structure[payment_type]"]').val(payment_structure.payment_type).change();


            if(payment_structure.payment_type === "percentage_of_route"){
                jQuery('input[name="payment_structure[percentage]"]').val(payment_structure.percentage);
            }            

            if(payment_structure.payment_type === "x_amount_per_appointement"){
                jQuery('input[name="payment_structure[amount_per_appointement]"]').val(payment_structure.amount_per_appointement);
            }
            
            if(payment_structure.payment_type === "percentage_of_route" || payment_structure.payment_type === "x_amount_per_appointement"){
                jQuery('input[name="payment_structure[monthly_maintenance]"]').val(payment_structure.monthly_maintenance);
                jQuery('input[name="payment_structure[quarterly_maintenance]"]').val(payment_structure.quarterly_maintenance);
                jQuery('input[name="payment_structure[special_maintenance]"]').val(payment_structure.special_maintenance);
                jQuery('input[name="payment_structure[commercial_maintenance]"]').val(payment_structure.commercial_maintenance);
            }

            if(payment_structure.payment_type === "fixed_weekly_salary"){
                jQuery('input[name="payment_structure[fixed_salary]"]').val(payment_structure.fixed_salary);
            }

            jQuery('input[name="employee_id"]').val(employe_id);
            jQuery('#createPaymentStructureModal').appendTo("body").modal('show');            

        }
        catch (error) {
            return false;
        }
    }
</script>
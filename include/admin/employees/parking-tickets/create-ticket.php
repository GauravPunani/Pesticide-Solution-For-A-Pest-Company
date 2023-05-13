<?php

$allowed_types = ['technician', 'door-to-door-salesman'];
$employee_types = (new Employee\Employee)->getEmployeeTypes($allowed_types);
$employees = (new Employee\Employee)->getAllEmployees($allowed_types);

if(count($employees) <= 0){
    echo "No employee in system to assign tickets to";
    return;
}

?>

<div class="card">
    <div class="card-body">
        <form id="createTicketForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <h3 class="page-header">Create Ticket</h3>

            <?php wp_nonce_field('create_parking_ticket'); ?>
            <input type="hidden" name="action" value="create_parking_ticket">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

            <div class="form-group">
                <label for="role_id">Select Employee Type</label>
                <select onchange="changeEmployeesByRole(this)" name="role_id" id="role_id" class="form-control select2-field">
                    <option value="">Select</option>
                    <?php if(is_array($employee_types) && count($employee_types) > 0): ?>
                        <?php foreach($employee_types as $type): ?>
                            <option value="<?= $type->id; ?>"><?= $type->name; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="employee_id">Select Employee</label>
                <select name="employee_id" id="employee_id" class="form-control">
                    <option value="">Select</option>
                    <?php foreach($employees as $employee): ?>
                        <option data-role-id="<?= $employee->role_id; ?>" value="<?= $employee->id ?>"><?= $employee->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="week">Select Week</label>
                <input class="form-control" type="week" name="week" id="week">
            </div>
            
            <div class="form-group">
                <label for="ticket_amount">Ticket Amount</label>
                <input type="text" class="form-control numberonly" name="ticket_amount" id="ticket_amount">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" cols="30" rows="5" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="proof_document">Proof Document</label>
                <input type="file" name="proof_document[]" id="proof_document" class="form-control" multiple>
            </div>

            <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Ticket</button>

        </form>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#createTicketForm').validate({
                rules: {
                    employee_id: "required",
                    week: "required",
                    ticket_amount: "required",
                    description: "required",
                    proof_document: "required",
                }
            })
        });
    })(jQuery);


    function changeEmployeesByRole(ref){
        const role_id = jQuery(ref).val();
        console.log('role id = '+role_id);



        jQuery("#employee_id > option").each(function() {

            if(role_id === undefined){
                jQuery(this).show();
                return;
            }            

            const employee_role_id = jQuery(this).attr('data-role-id');

            if(employee_role_id ===  undefined) return;

            console.log(' employee role id = '+ employee_role_id);

            if(role_id === employee_role_id){
                jQuery(this).show();
            }
            else{
                jQuery(this).hide();                
            }
        });        
    }
</script>
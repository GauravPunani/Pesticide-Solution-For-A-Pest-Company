<?php

if(isset($_GET['application_id']) && !empty($_GET['application_id'])){
    return get_template_part('/include/admin/employees/door-to-door-sales/verify-application', null, ['id' => $_GET['application_id']]);
}

if(isset($_GET['edit-id']) && !empty($_GET['edit-id'])){
    return get_template_part('/include/admin/employees/door-to-door-sales/edit-ac', null, ['sales_person_id' => $_GET['edit-id']]);
}

global $wpdb;

$conditions = [];

$conditions[] = " role_id = 4";

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'pending-application':
            $conditions[] = " application_status = 'pending'";
        break;
        case 'fired-sales-persons':
            $conditions[] = " application_status = 'fired'";
        break;
        case 'inactive-sales-persons':
            $conditions[] = " status = 0 and application_status = 'verified'";
        break;
        
        default:
            $conditions[] =" application_status = 'verified' and status = 1";
        break;
    }
}
else{
    $conditions[] =" application_status = 'verified' and status = 1";    
}

$conditions = (new GamFunctions)->generate_query($conditions);

$sales_persons = $wpdb->get_results("
    select *
    from {$wpdb->prefix}employees
    $conditions
");
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Sales Persons</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($sales_persons) && count($sales_persons) >0): ?>
                                <?php foreach($sales_persons as $sales_person): ?>
                                    <tr>
                                        <td><?= $sales_person->username; ?></td>
                                        <td><?= $sales_person->name; ?></td>
                                        <td><?= $sales_person->email; ?></td>
                                        <td><?= $sales_person->address; ?></td>
                                        <td><?= date('d M y', strtotime($sales_person->created_at)); ?></td>
                                        <td>
                                            <div class="dropdown">                                            
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&edit-id=<?= $sales_person->id; ?>"><span><i class="fa fa-edit"></i></span> Edit</a></li>

                                                    <?php if(isset($_GET['tab']) && $_GET['tab'] == "pending-application"): ?>
                                                        <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&application_id=<?= $sales_person->id; ?>"><span><i class="fa fa-check"></i></span> Verify Account</a></li>
                                                    <?php endif; ?>

                                                    <?php if(!isset($_GET['tab']) || $_GET['tab'] == "inactive-sales-persons"): ?>
                                                        <li><a onclick="fireEmployee(<?= $sales_person->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-ban"></i></span> Fire Employee</a></li>
                                                    <?php endif; ?>
													<?php if(isset($_GET['tab']) && ($_GET['tab'] == "pending-application")): ?>
                                                    <li><a onclick="deleteAccount('<?= $sales_person->id; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Account</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No Record Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Fire Employee</h4>
            </div>
            <div class="modal-body">
                <form id="fireEmployeeForm">

                    <?php wp_nonce_field('fire_employee'); ?>
                    <input type="hidden" name="action" value="fire_employee">
                    <input type="hidden" name="employee_id">

                    <div class="form-group">
                        <label for="fire_reason">Fire Reason</label>
                        <textarea name="fire_reason" id="fire_reason" cols="30" rows="5" class="form-control"></textarea>
                    </div>

                    <button class="btn btn-danger submitBtn"><span><i class="fa fa-ban"></i></span> Fire Employee</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    let ref_btn;

    function verifyAccount(employee_id, ref){
        if(!confirm('Are you sure you want to verify this account ?')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "verify_employee_account",
                employee_id,
                "_wpnonce": "<?= wp_create_nonce('verify_employee_account'); ?>"
            },
            beforeSend:function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success: function(data){

                alert(data.message);

                if(data.status === "success"){
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                else{
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                }
                
            }
        })

    }
	
	function deleteAccount(employee_id, ref){

        if(confirm('Are you sure, you want to delete this account?')){
            // hit ajax to delete account
            jQuery.ajax({
                type: 'post',
                url: "<?= admin_url('admin-ajax.php'); ?>",
                dataType: "json",
                data: {
                    action: "delete_employee_account",
                    employee_id: employee_id,
					"_wpnonce": "<?= wp_create_nonce('delete_employee_account'); ?>"
                },
                beforeSend: function() {
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                },
                success: function(data){
                    if(data.status == "success"){
                        alert(data.message);
                        jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                    }
                    else{
                        alert(data.message);
                        jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                    }
                }
            })
        }        

    }

    function fireEmployee(employee_id, ref){
        ref_btn = ref;

        jQuery('#fireEmployeeForm input[name="employee_id"]').val(employee_id);
        jQuery('#myModal').modal('show');
    }

    (function($){
        $(document).ready(function(){

            $('#fireEmployeeForm').validate({
                rules:{
                    fire_reason: "required"
                }
            });

            $('#fireEmployeeForm').on('submit', function(e){

                e.preventDefault();

                $.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    dataType: "json",
                    data: $(this).serialize(),
                    beforeSend:function(){
                        $(ref_btn).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                        $('.submitBtn').attr('disabled', true);
                    },
                    success: function(data){

                        alert(data.message);

                        if(data.status === "success"){
                            $(ref_btn).closest('.dropdown').parent().parent().fadeOut();
                        }
                        else{
                            $(ref_btn).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                        }

                        $('#fireEmployeeForm').trigger("reset");
                        $('.submitBtn').attr('disabled', false);
                        jQuery('#myModal').modal('hide');
                    }
                });
            })
        })
    })(jQuery);
</script>
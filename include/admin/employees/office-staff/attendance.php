<?php

global $wpdb;

$start_time = date("h:i a");

$conditions = [];

$conditions[] = " role_id = 3";

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'pending-verification':
            $conditions[] = " application_status = 'pending'";
        break;
        case 'fired':
            $conditions[] = " application_status = 'fired'";
        break;
        case 'inactive':
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

$staff_members = $wpdb->get_results("
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
                    <h3 class="page-header">Office Staff Attendance</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Started At</th>
                                <th>Mark Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($staff_members) && count($staff_members) >0): ?>
                                <?php foreach($staff_members as $staff_member): ?>
                                    <tr>
                                        <td><?= $staff_member->name; ?></td>

                                        <td><?= date('h:i:sa', strtotime($staff_member->start_time)); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <?php if ($staff_member->attendance == 1) { ?>
                                                    <a href="<?= $_SERVER['REQUEST_URI']; ?>&edit-id=<?= $staff_member->id; ?>"><button class="btn btn-success" >  Active</button></a>
                                                <?php } else { ?><a href="<?= $_SERVER['REQUEST_URI']; ?>&edit-id=<?= $staff_member->id; ?>"><button class="btn btn-danger" >  Start</button></a>
                                                <?php } ?>
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
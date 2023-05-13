<?php

global $wpdb;

// for technician details page 
if(isset($_GET['technician-id']) && !empty($_GET['technician-id'])){
    get_template_part('include/admin/technician/technician-details', null, ['id' => $_GET['technician-id']]);
    return;
}
if(isset($_GET['edit-id']) && !empty($_GET['edit-id'])){
    get_template_part('include/admin/technician/edit-technician', null, ['id' => $_GET['edit-id']]);
    return;
}

$conditions=[];

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    if(isset($_GET['view']) && $_GET['view']!="pending-applications"){
        $conditions[]=" branch_id IN ($accessible_branches)";
    }
}

if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id']!="all"){
    $conditions[]=" branch_id = '{$_GET['branch_id']}'";
}

if(isset($_GET['view'])){
    switch ($_GET['view']) {
        case 'pending-applications':
            $conditions[]=" application_status='pending'";
        break;
        case 'rejected-applications':
            $conditions[]=" application_status='rejected'";
        break;
        case 'fired-technician':
            $conditions[]=" application_status='fired'";
        break;
        case 'resigned-technician':
            $conditions[]=" application_status='resigned'";
        break;
        
        default:
            $conditions[]=" application_status='verified'";
        break;
    }
}
else{
    $conditions[]=" application_status='verified'";
}

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

$gam_technicians=$wpdb->get_results("
    select * from 
    {$wpdb->prefix}technician_details 
    $conditions
");

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h4 class="page-header">GAM Employees</h4>
                    <?php if(isset($_GET['view']) && $_GET['view']!="pending-applications"): ?>
                        <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
                    <?php endif; ?>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>D.O.B</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($gam_technicians) && count($gam_technicians) >0): ?>
                                <?php foreach($gam_technicians as $technician): ?>
                                    <tr>
                                        <td><?= $technician->first_name." ".$technician->last_name; ?></td>
                                        <td><?= $technician->email; ?></td>
                                        <td><?= $technician->dob; ?></td>
                                        <td><?= $technician->address; ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&technician-id=<?= $technician->id; ?>"><span><i class="fa fa-eye"></i></span> View</a></li>
                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&edit-id=<?= $technician->id; ?>"><span><i class="fa fa-edit"></i></span> Edit</a></li>
                                                    <?php if((new Technician_details)->check_if_locked_by_office($technician->id)): ?>
                                                        <li><a onclick="unlockAc('<?= $technician->id; ?>',this)" href="#"> <span><i class="fa fa-unlock"></i></span> Unlock Ac</a></li>
                                                    <?php else: ?>
                                                        <li><a onclick="freezeAc('<?= $technician->id; ?>',this)" href="javascript:void(0)"> <span><i class="fa fa-ban"></i></span> Freeze Ac</a></li>
                                                    <?php endif; ?>
                                                    <li><a data-tech-id="<?= $technician->id; ?>" class="changepassword" href="javascript:void(0)"><span><i class="fa fa-key"></i></span> Change Password</a></li>
                                                    <?php if(isset($_GET['view']) && ($_GET['view'] == "pending-applications" || $_GET['view'] == "rejected-applications")): ?>
                                                    <li><a onclick="deleteApplication('<?= $technician->id; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Application</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>                                          
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No Record Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Free Tech Modal -->
<div id="free_tech_ac_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Freeze Technician Account</h4>
      </div>
      <div class="modal-body">
        <form id="freeze_ac_form" action="<?= admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="freeze_tech_account_by_office">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
            <input type="hidden" name="technician_id" value="">
            <div class="form-group">
                <label for="">Please provide reson for account freeze</label>
                <textarea name="freeze_reason"  cols="30" rows="5" class="form-control"></textarea>
            </div>
            <button class="btn btn-danger"><span><i class="fa fa-ban"></i></span> Freeze Account</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- Change Password Modal -->
<div id="change_password_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Change Technician Account Passowrd</h4>
      </div>
      <div class="modal-body">
        <form id="change_tech_password_form" action="<?= admin_url('admin-post.php'); ?>" method="post">
			<?php wp_nonce_field('change_tech_password'); ?>
            <input type="hidden" name="action" value="change_tech_password">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
            <input type="hidden" name="technician_id" value="">

            <div class="form-group">
                <label for="">New Password</label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="on">
            </div>

            <div class="form-group">
                <label for="">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" autocomplete="on">
            </div>

            <button class="btn btn-danger"><span><i class="fa fa-key"></i></span> Change Password</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>

    (function($){
        $(document).ready(function(){
            $('.changepassword').on('click',function(){
                let tech_id=$(this).attr('data-tech-id');
                $('input[name="technician_id"]').val(tech_id);

                $('#change_password_modal').modal('show');

            });

            $('#change_tech_password_form').validate({
                rules:{
                    password:{
                        required:true,
                        minlength : 8
                    },
                    confirm_password : {
                        minlength : 8,
                        equalTo : "#password"
                    },
                },
                messages:{
                    confirm_password:{
                        equalTo:"password & verify password field do not match"
                    },
                }
            })
        })
    })(jQuery);

    function deleteApplication(technician_id, ref){

        if(confirm('Are you sure, you want to delete this technician application?')){
            // hit ajax to delete technician application
            jQuery.ajax({
                type: 'post',
                url: "<?= admin_url('admin-ajax.php'); ?>",
                dataType: "json",
                data: {
                    action: "delete_technician_application",
                    technician_id: technician_id,
                    "_wpnonce": "<?= wp_create_nonce('delete_technician_application'); ?>"
                },
                beforeSend: function() {
                    jQuery(ref).parent().addClass('disabled');
                },
                success: function(data){
                    if(data.status == "success"){
                        alert(data.message);
                        jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                    }
                    else{
                        alert(data.message);
                        jQuery(ref).parent().removeClass('disabled');
                    }
                }
            })
        }        

    }

    function freezeAc(tech_id,ref){

        // put technician id in form 
        jQuery('#freeze_ac_form input[name="technician_id"]').val(tech_id);

        // open modal for freeze ac
        jQuery('#free_tech_ac_modal').modal('show');
    }

    function unlockAc(tech_id,ref){

        let obj=ref;

        jQuery.ajax({
            type:"post",
            url:"<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action:"unlock_ac_by_office",
                technician_id:tech_id
            },
            dataType:"json",
            beforeSend:function(){
                jQuery(obj).attr('disabled',true)
            },
            success:function(data){
                if(data.status=="success"){
                    window.location.reload();
                }
                else{
                    alert('Something went wrong, please try again later');
                    jQuery(obj).attr('disabled',false);
                }
            }
        })
    }

</script>
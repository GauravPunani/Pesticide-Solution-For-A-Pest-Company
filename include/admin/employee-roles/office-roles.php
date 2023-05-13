<?php
    global $wpdb;
    $conditions=[];

    if(!empty($_GET['employee_id'])) $conditions[] = " R.employee_id = '{$_GET['employee_id']}' ";
    if(!empty($_GET['assign_status'])){
        if($_GET['assign_status'] == "not_assigned") $conditions[] = " R.employee_id = '' or R.employee_id is null ";
        if($_GET['assign_status'] == "assigned") $conditions[] = " R.employee_id is not null ";
    }

    
    $conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : ''; 

    $pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;

    $no_of_records_per_page = 50;
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_rows = $wpdb->get_var("
        select R.*, E.name as employee_name, count(ORE.role_id) as linked_employee_count
        from {$wpdb->prefix}roles R

        left join {$wpdb->prefix}office_role_employee ORE
        on ORE.role_id = R.id

        left join {$wpdb->prefix}employees E
        on R.employee_id = E.id

        $conditions
        group by R.id
    ");
    
    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $roles = $wpdb->get_results("
        select R.*, E.name as employee_name, count(ORE.role_id) as linked_employee_count
        from {$wpdb->prefix}roles R

        left join {$wpdb->prefix}office_role_employee ORE
        on ORE.role_id = R.id

        left join {$wpdb->prefix}employees E
        on R.employee_id = E.id

        $conditions
        group by R.id
        order by R.created_at desc
        LIMIT $offset, $no_of_records_per_page
    ");

    $allowed_employees = (new Employee\Employee)->getAllEmployees(['office_staff', 'cold_caller']);
?>

<div class="container-fluid">
    <div class="row">
            <div class="col-sm-12">                            
                <div class="card full_width table-responsive">
                    <div class="card-body">

                        <?php (new GamFunctions)->getFlashMessage(); ?>
                            <h3 class="page-header">Office Roles <small>(<?= $total_rows; ?> Records Found)</small></h3>
                            <p class="text-info"></p>
                            <button type="button" onclick="createRole()" class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Role</button>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Linked Employees Count</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($roles) && count($roles)>0): ?>
                                <?php foreach($roles as $role): ?>
                                    <tr>
                                        <td><?= $role->name; ?></td>
                                        <td><?= $role->linked_employee_count; ?></td>
                                        <td><?= date('d M Y h:i:A', strtotime($role->created_at)); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a onclick="editRole('<?= htmlspecialchars(json_encode($role), ENT_QUOTES, 'UTF-8'); ?>', this)" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Edit Role</a></li>

                                                    <li><a onclick="deleteOfficeStaffRole(<?= $role->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Role</a></li>

                                                    <li><a onclick="javascript:alert('<?= $role->slug; ?>')" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View Role Slug</a></li>

                                                </ul>
                                            </div>                                                
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8">No Record found</td></tr>
                            <?php endif; ?>  
                            </tbody>
                        </table>
                        <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                    </div>
                </div>
            </div>
    </div>
</div>

<!-- EDIT Role MODAL -->
<div id="editRoleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit Role</h4>
        </div>
        <div class="modal-body">
            <form id="editRole" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('update_office_role'); ?>
				<input type="hidden" name="action" value="update_office_role">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="role_id" value="">
                                    
                <div class="form-group">
                    <label for="">Role Name</label>
                    <input type="text" class="form-control" name="role_name">
                </div>
                
                <p>Previously linked employees will be remained linked as well. Selecting more employee from below dropdown will link more employees to this role</p>
                <div class="form-group">
                    <label for="">Select Employees (optional)</label>
                    <select name="employee_ids[]" class="form-control select2-field" multiple> 
                        <option value="">Select</option>
                        <?php if(is_array($allowed_employees) && count($allowed_employees) >0): ?>
                            <?php foreach($allowed_employees as $employee): ?>
                                <option value="<?= $employee->id; ?>"><?= $employee->name; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Update Type</button>
                    
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
</div>
<!-- end edit modal -->

<!-- CREATE ROLE MODAL  -->
<div id="createRoleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Role</h4>
            </div>
            <div class="modal-body">
                <form id="createRoleForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                    <?php wp_nonce_field('create_office_staff_role'); ?>
                    <input type="hidden" name="action" value="create_office_staff_role">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <div class="form-group">
                        <label for="">Role Name</label>
                        <input type="text" name="role_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="">Select Employees (optional)</label>
                        <select name="employee_ids[]" class="form-control select2-field" multiple> 
                            <option value="">Select</option>
                            <?php if(is_array($allowed_employees) && count($allowed_employees) >0): ?>
                                <?php foreach($allowed_employees as $employee): ?>
                                    <option value="<?= $employee->id; ?>"><?= $employee->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>                        

                    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Role</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
<!-- End Create role -->

<script>

    function deleteOfficeStaffRole(role_id, ref){
        if(!confirm('Are you sure you want to delete this role ? Deleting role will also delete linked users with this role.')) return;
        
        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data: {
                action: "delete_office_staff_role",
                role_id,
                "_wpnonce": "<?= wp_create_nonce('delete_office_staff_role'); ?>"
            },
            dataType: "json",
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success: function(data){
                if(data.status === "success"){
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                }
            }
        });
    }

    function createRole(){
        jQuery('#createRoleModal').modal('show');
    }

    function editRole(role_data, ref){
        role_data = jQuery.parseJSON(role_data);
        console.log(role_data);

        jQuery('#editRole input[name="role_id"]').val(role_data.id);
        jQuery('#editRole input[name="role_name"]').val(role_data.name);

        jQuery('#editRoleModal').modal('show');

    }

    (function($){
        $(document).ready(function(){
            $('#createRoleForm').validate({
                rules: {
                    role_name: "required",
                }
            });

            $('#editRole').validate({
                rules: {
                    role_name: "required",
                }
            })

        });
    })(jQuery);
</script>
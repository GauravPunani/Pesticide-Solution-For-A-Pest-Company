<?php
global $wpdb;

if(!empty($_GET['edit_role_id'])) return get_template_part('include/admin/employees/cold-caller/roles/edit-role', null, ['role_id' => $_GET['edit_role_id']]);

$meta_roles = (new ColdCaller)->getColdCallersTypes();
$branches = (new Branches)->getAllBranches();
$cold_callers = (new Employee\Employee)->getAllEmployees(['cold_caller']);

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}cc_role_meta CRM

    left join {$wpdb->prefix}cold_caller_types CCT
    on CRM.role_id = CCT.id

    left join {$wpdb->prefix}branches B
    on CRM.branch_id = B.id

    where CRM.count - (
        select count(*)
        from {$wpdb->prefix}cc_role_relation CRR
        where CRR.role_id = CRM.id
    ) > 0

");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$roles = $wpdb->get_results("

    select CRM.*, B.location_name, CCT.name, (
        select count(*)
        from {$wpdb->prefix}cc_role_relation CRR
        where CRR.role_id = CRM.id
    ) as assigned_count
    from {$wpdb->prefix}cc_role_meta CRM

    left join {$wpdb->prefix}cold_caller_types CCT
    on CRM.role_id = CCT.id

    left join {$wpdb->prefix}branches B
    on CRM.branch_id = B.id

    where CRM.count - (
        select count(*)
        from {$wpdb->prefix}cc_role_relation CRR
        where CRR.role_id = CRM.id
    ) > 0
    LIMIT $offset, $no_of_records_per_page
");

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Available Roles</h3>
                    <button onclick="createRole()" class="btn btn-primary pull-right"><span><i class="fa fa-plus"></i></span> Create Role</button>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Count</th>
                                <th>Available Count</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($roles) && count($roles) > 0): ?>
                                <?php foreach($roles as $role): ?>
                                    <tr>
                                        <td><?= $role->location_name." ".$role->name; ?></td>
                                        <td><?= $role->count; ?></td>
                                        <td><?= $role->count - $role->assigned_count; ?></td>
                                        <td><?= date('d M Y h:i A', strtotime($role->created_at)); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="assignRole(<?= $role->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-link"></i></span> Assign Cold Caller</a></li>
                                                    <li><a href="<?= $_SERVER['REQUEST_URI']."&edit_role_id=".$role->id; ?>"><span><i class="fa fa-edit"></i></span> Edit Role</a></li>
                                                    <li><a onclick="deleteRole(<?= $role->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Role</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No role available to assign to cold callers</td>
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

                    <?php wp_nonce_field('create_cold_caller_role'); ?>
                    <input type="hidden" name="action" value="create_cold_caller_role">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">


                    <div class="form-group">
                        <label for="">Select Type</label>
                        <small><i>If type not listed, create new type from <b>Cold Caller Types -> Create New type</b> form.</i></small>
                        <select name="role_id" class="form-control select2-field">
                            <option value="">Select</option>
                            <?php if(is_array($meta_roles) && count($meta_roles) > 0): ?>
                                <?php foreach($meta_roles as $meta_role): ?>
                                    <option value="<?= $meta_role->id; ?>"><?= $meta_role->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="">Select Branch</label>
                        <select name="branch_id" class="form-control select2-field">
                            <option value="">Select</option>
                            <?php if(is_array($branches) && count($branches) > 0): ?>
                                <?php foreach($branches as $branch): ?>
                                    <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>                            
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="">Role Count</label>
                        <input type="text" class="form-control numberonly" name="count">
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

<!-- ASSIGN ROLE MODAL  -->
<div id="assignRoleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Assign role to cold caller</h4>
            </div>
            <div class="modal-body">
                <form id="assignRoleForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                    <?php wp_nonce_field('assign_cold_caller_role'); ?>
                    <input type="hidden" name="action" value="assign_cold_caller_role">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="role_id">

                    <div class="form-group">
                        <label for="">Select Cold Caller</label>
                        <select name="cold_caller_id" class="form-control select2-field">
                            <option value="">Select</option>
                            <?php if(is_array($cold_callers) && count($cold_callers) > 0): ?>
                                <?php foreach($cold_callers as $cold_caller): ?>
                                    <option value="<?= $cold_caller->id; ?>"><?= $cold_caller->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Assign Role</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    function createRole(){
        jQuery('#createRoleModal').modal('show');
    }

    function assignRole(role_id){
        jQuery('#assignRoleForm input[name="role_id"]').val(role_id);
        jQuery('#assignRoleModal').modal('show');
    }

    function deleteRole(role_id, ref){
        if(!confirm('Are you sure you want to delete this role? deleting role will also removed linke cold callers records for this role.')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "delete_cold_caller_role",
                role_id,
                "_wpnonce": "<?= wp_create_nonce('delete_cold_caller_role'); ?>"
            },
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success:function(data){
                if(data.status === "error"){
                    alert(data.message);
                }
                else{
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            }
        })
    }

    (function($){
        $(document).ready(function(){
            $('#createRoleForm').validate({
                rules: {
                    role_id: "required",
                    branch_id: "required",
                    count: "required",
                }
            });

            $('#assignRoleForm').validate({
                rules: {
                    cold_caller_id: "required",
                }
            });
        });
    })(jQuery);

</script>
<?php

if(empty($args['role_id'])) return;

$role_id = sanitize_text_field($args['role_id']);

$role_data = (new ColdCallerRoles)->getRole($role_id);

if(!$role_data) return;

$meta_roles = (new ColdCaller)->getColdCallersTypes();
$branches = (new Branches)->getAllBranches();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Edit Role</h3>
                    <form id="editRoleForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                        <?php wp_nonce_field('edit_cold_caller_role'); ?>
                        <input type="hidden" name="action" value="edit_cold_caller_role">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <input type="hidden" name="record_id" value="<?= $role_data->id; ?>">

                        <div class="form-group">
                            <label for="">Select Role</label>
                            <select name="role_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($meta_roles) && count($meta_roles) > 0): ?>
                                    <?php foreach($meta_roles as $meta_role): ?>
                                        <option value="<?= $meta_role->id; ?>" <?= $role_data->role_id == $meta_role->id ? 'selected' : ''; ?>><?= $meta_role->name; ?></option>
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
                                        <option value="<?= $branch->id; ?>" <?= $role_data->branch_id == $branch->id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>                            
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Role Count</label>
                            <input type="text" class="form-control numberonly" name="count" value="<?= $role_data->count; ?>">
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Role</button>
                    </form>                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#editRoleForm').validate({
                rules: {
                    role_id: "required",
                    branch_id: "required",
                    count: "required",
                }
            });            
        });
    })(jQuery);
</script>
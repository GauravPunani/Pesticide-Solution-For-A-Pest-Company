<?php
global $wpdb;


if(isset($_GET['active_role']) && !empty($_GET['active_role'])){
    $conditions[] = " ER.slug = '{$_GET['active_role']}'";
}
else{
    $conditions[] = " ER.slug = 'office_staff'";
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$employees = $wpdb->get_results("
    select E.*
    from {$wpdb->prefix}employees E
    left join {$wpdb->prefix}employees_types ER
    on E.role_id = ER.id
    $conditions
");

$all_employees = $wpdb->get_results("
    select * from {$wpdb->prefix}employees WHERE application_status = 'verified'
");


?>

<div class="container">
    <div class="row">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <!-- CREATE TASK FORM  -->
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form id="createTaskForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
						<?php wp_nonce_field('create_task'); ?>
                        <input type="hidden" name="action" value="create_task">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <div class="form-group">
                            <label for="">Please describe task <span class="text-danger">*</span></label>
                            <textarea name="task_description" rows="5" class="form-control" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Select Employee</label>
                            <select name="performer" class="form-control select2-field">
                                <option value="">Select</option> 
                                <?php if(is_array($all_employees) && count($all_employees) >0): ?>
                                    <?php foreach($all_employees as $all_emps): ?>
                                        <option value="<?= $all_emps->id; ?>"><?= $all_emps->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>                        

                        <div class="form-group">
                            <label for=""><span><i class="fa fa-paperclip"></i></span> Task Attachements</label>
                            <input type="file" name="files[]" class="form-control" multiple>
                        </div>
                        
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Task</button>
                    </form>
				</div>
            </div>
        </div>

    </div>
</div>

<script>
    (function($){
        $('#createTaskForm').validate({
            rules: {
                task_description: "required"
            }
        })
    })(jQuery);
</script>
<?php

global $wpdb;

    $conditions=[];

    if(!empty($_GET['status'])) $conditions[] = " T.task_status = '{$_GET['status']}' ";
    if(!empty($_GET['date_created'])) $conditions[]=" DATE(Task.created_at)='{$_GET['date_created']}' ";

    if(!empty($_GET['search'])){
        $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'tasks');
        $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'T');
    }

    $conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : "";

    $pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
    $no_of_records_per_page =20;
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_rows = $wpdb->get_var("
        select count(*)
        from {$wpdb->prefix}tasks T
        $conditions
    ");

    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $tasks = $wpdb->get_results("
        select T.*
        from {$wpdb->prefix}tasks T
        $conditions
        order by T.created_at desc
        LIMIT $offset, $no_of_records_per_page
    ");

    $all_employees = (new Employee\Employee)->getAllEmployees();

    $task_employees = [];
    foreach($tasks as $task){
        $task_employees[$task->id] = (new Task_manager)->getTaskEmployees($task->id);
    }

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>

            <!-- FILTERS -->
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form id="filtersForm">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <!-- STATUS  -->
                        <div class="form-group">
                            <label for="technician">Status</label>
                            <select class="form-control select2-field" name="status" >
                                <option value="">All</option>
                                <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?= isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>

                        <!-- DATE CREATED -->
                        <div class="form-group">
                            <label for="">Date Created</label>
                            <input type="date" class="form-control" name="date_created" value="">
                        </div>

                        <!-- SEARCH RECORDS  -->
                        <div class="form-group">
                            <label for="">Search Records</label>
                            <input type="text" name="search" value="<?= @$_GET['search']; ?>" class="form-control">
                        </div>

                        <p><a onclick="resetFilters('filtersForm')" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Reset Filters</a></p>
                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                    </form>
                </div>     
            </div>

            <!-- TASKS LISTING -->
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Tasks <small>(<?= $total_rows; ?> records found)</small></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Task Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if( is_array($tasks) && !empty($tasks)): ?>
                                <?php foreach($tasks as $task): ?>
                                    <tr>
                                        <td>
                                            <?= stripslashes($task->task_description); ?>
                                            <p>-------------------------------------</p>
                                            <p><b>Task Employees : </b> <?= implode(', ', $task_employees[$task->id]); ?></p>
                                        </td>
                                        <td><h4><span class="<?= $task->task_status == "pending" ? 'label label-danger' : 'label label-success'; ?>"><?= $task->task_status; ?></span></h4></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a onclick="deleteTask(<?= $task->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Task</a></li>

                                                    <li><a onclick="updateTaskStatus(<?= $task->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Update Task Status</a></li>

                                                    <li><a data-task-files='<?= $task->task_document ?>' onclick="viewTaskFiles(this)" href="javascript:void(0)"><span><i class="fa fa-paperclip"></i></span> Task Files</a></li>

                                                    <li><a data-task-id='<?= $task->id ?>' data-docs='<?= $task->task_proof_doc ?>' data-notes="<?= $task->notes; ?>" onclick="showProof(this)" href="javascript:void(0)"><span><i class="fa fa-paperclip"></i></span> Performer Proof</a></li>

                                                    <?php if($task->task_status == 'pending'): ?>
                                                        <li><a onclick="assignToEmployees(<?= $task->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-link"></i></span> Assign To Employee</a></li>
                                                    <?php endif; ?>

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

<div id="perfoermProofModal" class="modal fade" role="dialog"> 
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
     <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Document Proofs </h4> 
      </div>
      <div class="modal-body">
            <div class="performerProofFiles"></div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- TASK DOCS -->
<div id="taskFilesModal" class="modal fade" role="dialog"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Task Files</h4> 
            </div>
            <div class="modal-body" id="taskFiles"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="updateTaskStatusModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Update Task Status</h4>
            </div>
            <div class="modal-body">
                <form id="updateTaskStatusForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

                    <?php wp_nonce_field('tm_update_task_status_by_office'); ?>

                    <input type="hidden" name="action" value="tm_update_task_status_by_office">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="task_id" value="">

                    <div class="form-group">
                        <label for="">Notes*</label>
                        <textarea name="notes" cols="30" rows="5" class="form-control" required=""></textarea> 
                    </div>

                    <div class="form-group">
                        <label for="">Select Status*</label>
                        <select name="status" class="form-control select2-field">
                            <option value="">Select Status</option>';
                            <option value="pending" >Pending</option>
                            <option value="completed" >Completed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="">Task Document</label>
                        <input type="file" name="task_proof_doc[]" class="form-control" multiple>
                    </div>
                    
                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Update Status</button>
                        
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="assignToEmployeesModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Update Task Status</h4>
            </div>
            <div class="modal-body">
                <form id="assignToeEmployeeForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

                    <?php wp_nonce_field('tm_assign_task_to_employees'); ?>

                    <input type="hidden" name="action" value="tm_assign_task_to_employees">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <input type="hidden" name="task_id" value="">
                    
                    <div class="form-group">
                        <label for="">Select Employees</label>
                        <select name="employees_ids[]" class="form-group select2-field" multiple>
                            <?php foreach($all_employees as $employee): ?>
                                <option value="<?= $employee->id; ?>"><?= $employee->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Update Status</button>
                        
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>

    const assignToEmployees = (task_id) => {
        jQuery('#assignToeEmployeeForm input[name="task_id"]').val(task_id)
        jQuery('#assignToEmployeesModal').modal('show')
    }

    function deleteTask(task_id, ref){
        if(!confirm('Are you sure you want to delete this task ?')) return;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data: {
                action: "delete_task",
                task_id,
                "_wpnonce": "<?= wp_create_nonce('delete_task'); ?>"
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

    const updateTaskStatus = (task_id) => {
        jQuery('#updateTaskStatusForm input[name="task_id"]').val(task_id);
        jQuery('#updateTaskStatusModal').modal('show');
    }

    const showProof = (ref) => {
        const task_docs = jQuery(ref).attr('data-docs');
        const task_notes = jQuery(ref).attr('data-notes');

        proof_html="<p><b>Notes</b></p>";
        proof_html+=`<p>${task_notes}</p>`;
        proof_html+= generateDocsHtml(task_docs);

        jQuery('.performerProofFiles').html(proof_html);
        jQuery('#perfoermProofModal').modal('show');
    }

    const viewTaskFiles = (ref) => {
        const task_files = jQuery(ref).attr('data-task-files');
        const task_files_html = generateDocsHtml(task_files);
        jQuery('#taskFiles').html(task_files_html);
        jQuery('#taskFilesModal').modal('show');
    }


    (function($){
        $(document).ready(function(){

            $('#updateTaskStatusForm').validate({
                rules: {
                    notes: "required",
                    status: "required"
                }
            })


        });
    })(jQuery);

</script>
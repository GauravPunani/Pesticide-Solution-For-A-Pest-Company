<?php

$employee_id = (new Employee\Employee)->__getLoggedInEmployeeId();

$conditions = [];

$conditions[] = " TE.performer_id = '$employee_id' ";

if(!empty($_GET['status'])){
    if($_GET['status'] == 'pending') $conditions[] = " T.task_status = 'pending' ";
    elseif($_GET['status'] == 'completed') $conditions[] = " T.task_status = 'completed' ";
}
else{
    $conditions[] = " T.task_status = 'pending' ";
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}tasks T

    left join {$wpdb->prefix}task_employee TE
    on TE.task_id = T.id

    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$tasks = $wpdb->get_results("
    select T.*
    from {$wpdb->prefix}tasks T

    left join {$wpdb->prefix}task_employee TE
    on TE.task_id = T.id

    $conditions
    order by T.created_at desc
    LIMIT $offset, $no_of_records_per_page
");
(new Navigation)->employeeTaskNavigation(@$_GET['status']);
?>

<h3 class="page-header">Tasks <small>(<?= $total_rows; ?> Records Found)</small></h3>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Task</th>
            <th>Notes</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if(is_array($tasks) && count($tasks) > 0): ?>
            <?php foreach($tasks as $task): ?>
                <tr>
                    <td><?= $task->task_description; ?></td>
                    <td><?= nl2br($task->notes); ?></td>
                    <td><?= date('d M Y h:i:A', strtotime($task->created_at)); ?></td>
                    <td><?= $task->task_status; ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                            <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                <?php if($task->task_status == 'pending'): ?>
                                    <li><a onclick="updateTaskStatus(<?= $task->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-refresh"></i></span> Update Task Status</a></li>
                                <?php endif; ?>

                                <?php if($task->task_status == 'completed'): ?>
                                    <li><a data-task-notes="<?= stripslashes(htmlspecialchars($task->notes, ENT_QUOTES)); ?>" data-proof-docs="<?= htmlspecialchars($task->task_proof_doc); ?>" onclick="viewProof(this)" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View Proof</a></li>
                                <?php endif; ?>

                                <li><a data-task-files="<?= $task->task_document; ?>" onclick="viewTaskFiles(this)" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View Task Files</a></li>

                                <li><a data-task-files="<?= $task->task_document; ?>" onclick="uploadNotes(<?= $task->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-upload"></i></span> Upload Notes</a></li>

                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>


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

<div id="taskProofModal" class="modal fade" role="dialog"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Task Proof</h4> 
            </div>
            <div class="modal-body" id="taskProof"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="updateTaskModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Update Task</h4>
        </div>
        <div class="modal-body">
            <form id="updateTaskForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

                <?php wp_nonce_field('tm_update_task_by_employee'); ?>

                <input type="hidden" name="action" value="tm_update_task_by_employee">

                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="task_id">

                <div class="form-group">
                    <label for="">Status</label>
                    <select class="form-control select2-field" disabled>
                        <option value="" selected>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Notes for office*</label>
                    <textarea name="notes" cols="30" rows="5" class="form-control" required=""></textarea> 
                </div>

                <div class="form-group">
                    <label for="">Upload Proof Doc</label>
                    <input type="file" name="task_proof_doc[]" class="form-control" multiple>
                </div>
                
                <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Update</button>
                    
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
</div>

<script>

    const uploadNotes = async (task_id) => {
        const { value: notes } = await Swal.fire({
            title: 'Notes for office',
            input: 'textarea',
            inputLabel: 'These notes will override any old notes already submitted',
            inputPlaceholder: 'Type your notes here...',
            inputAttributes: {
                'aria-label': 'Type your notes here',
                required: true
            },
        showCancelButton: true
        })

        if (notes) ajaxUploadNotes(task_id, notes);
    }

    const ajaxUploadNotes = (task_id, notes) => {
        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: 'json',
            data: {
                action: "tm_upload_notes_by_employee",
                task_id,
                notes
            },
            beforeSend: function(){
                showLoader('Saving notes to database...');
            },
            success : function (data){
                if(data.status == "success"){
                    new swal({
                            title: "Success!", 
                            text: "Office notes uploaded in system successfully", 
                            type: "success"
                        }
                    ).then(function(){
                        location.reload();
                    })
                }
                else{
                    new swal('Oops!', data.message, 'error');
                }                
            },
            error: function(){
                new swal('Oops!', 'Something went wrong, please try again later', 'error');
            }
        })
    }

    function viewProof(ref){
        const task_proof_docs = jQuery(ref).attr('data-proof-docs');
        const task_notes = jQuery(ref).attr('data-task-notes')
        const task_proof_docs_html = generateDocsHtml(task_proof_docs)

        const task_proof_html  = `
            <p><b>Notes</b></p>
            <p>${task_notes}</p>
            <p><b>Task Proof Docs</b></p>
            ${task_proof_docs_html}
        `;


        jQuery('#taskProof').html(task_proof_html)
        jQuery('#taskProofModal').modal('show')
    }

    function viewTaskFiles(ref){
        const task_files = jQuery(ref).attr('data-task-files');
        const task_files_html = generateDocsHtml(task_files);
        jQuery('#taskFiles').html(task_files_html);
        jQuery('#taskFilesModal').modal('show');
    }

    function updateTaskStatus(task_id){
        jQuery('#updateTaskForm input[name="task_id"]').val(task_id)
        jQuery('#updateTaskModal').modal('show');
    }

    (function($){
        $(document).ready(function(){
            $('#updateTaskForm').validate({
                rules:{
                    notes: 'required'
                }
            })
        })
    })
</script>
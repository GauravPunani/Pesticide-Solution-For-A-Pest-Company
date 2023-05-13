<?php
global $wpdb;

$cold_caller_id = (new ColdCaller)->getLoggedInColdCallerId();
$user = (new ColdCaller)->getColdCallerById($cold_caller_id);

$employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);

$conditions=[];
    
if(!empty($_GET['requester_id'])){
    $requester_id = urldecode($_GET['requester_id']);
    $conditions[] = " Taskemployee.emp_requester_id = '$requester_id'";
}

if(!empty($_GET['date_created'])){
    $conditions[]="DATE({$wpdb->prefix}tasks.created_at)='{$_GET['date_created']}'";
}

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

if(!empty($_GET['status'])){
    $status=$_GET['status'];
    $conditions[] =" (task_status='$status' or task_status IS NULL)";
}

if(!empty($_GET['search'])){
    if(!empty($conditions)){
        $conditions[] = " (task_description LIKE '%".$_GET['search']."%' OR task_document LIKE '%".$_GET['search']."%' OR task_proof_doc LIKE '%".$_GET['search']."%' OR task_status LIKE '%".$_GET['search']."%' OR notes LIKE '%".$_GET['search']."%' )";
    }
    else{
        $conditions[] = " (task_description LIKE '%".$_GET['search']."%' OR task_document LIKE '%".$_GET['search']."%' OR task_proof_doc LIKE '%".$_GET['search']."%' OR task_status LIKE '%".$_GET['search']."%' OR notes LIKE '%".$_GET['search']."%' )";
    }    
}


if(!empty($employee_id)){
    $conditions[] = "Taskemployee.performer_id='$employee_id'";
}

if(isset($_GET['tab']) && $_GET['tab'] == "completed"){
    $conditions[] = "{$wpdb->prefix}tasks.task_status='completed'";
}else{
    $conditions[] = "{$wpdb->prefix}tasks.task_status='pending'";
}

if(count($conditions)>0){
    $conditions = (new GamFunctions)->generate_query($conditions);
}
else{
    $conditions = "";
}

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}


$no_of_records_per_page =20;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows= $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}tasks
    inner join {$wpdb->prefix}task_employee Taskemployee
    on {$wpdb->prefix}tasks.id=Taskemployee.task_id
    $conditions
");


$total_pages = ceil($total_rows / $no_of_records_per_page);

//calculate recorde index by page no
$records_starting_index=(($pageno-1)*$no_of_records_per_page)+1;

        
$tasks = $wpdb->get_results("
        select {$wpdb->prefix}tasks.*,Taskemployee.*,Employee.name AS emp_name,Employee2.name AS per_name
        from {$wpdb->prefix}tasks

        inner join {$wpdb->prefix}task_employee Taskemployee
        on {$wpdb->prefix}tasks.id=Taskemployee.task_id

        left join {$wpdb->prefix}employees Employee
        on Taskemployee.emp_requester_id=Employee.id

        left join {$wpdb->prefix}employees Employee2
        on Taskemployee.performer_id=Employee2.id
        
        $conditions
        ORDER BY FIELD(task_status, 'pending') DESC
        LIMIT $offset, $no_of_records_per_page
");

$all_employees = $wpdb->get_results("
    select * 
    from {$wpdb->prefix}employees 
    WHERE application_status = 'verified'
");

// echo "<pre>"; print_r($tasks); die;
?>
<div class="table-responsive">
    <h1 class="text-center">Tasks</h1>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <?php if(isset($_GET['search']) || isset($_GET['requester_id']) || isset($_GET['status']) || isset($_GET['date_created'])): ?>
                    <p class="alert alert-success alert-dismissible">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b><br>    
                        <?php endif; ?>
                        <?php if(isset($_GET['status']) && !empty($_GET['status'])): ?>
                            <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['status']; ?></b><br>
                        <?php endif; ?>
                        <a class="btn btn-info" href="<?= strtok($_SERVER["REQUEST_URI"], '?'); ?>?view=view-task"><span><i class="fa fa-database"></i></span> Show All Records</a>
                    </p>
                <?php endif; ?>
                
            </div>
            <!-- RECORD LISTING AND FILTER  -->
            
            <div class="col-sm-12 col-md-12">
                <div class="card full_width table-responsive">
                    <div class="card-body">                              
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Requester</th>
                        <th>Task Description</th>
                        <th>Task Docs</th>
                        <th>Task Proofs</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(is_array($tasks) && count($tasks)>0): ?>
                        <?php foreach($tasks as $task): ?>
                            <tr>
                                <td><?= $task->emp_name; ?></td>
                                <td><?= nl2br($task->task_description); ?> </td>
                                <td>
                                <?php if(!empty($task->task_document)): ?>
                                    <button data-docs='<?= $task->task_document; ?>' class='btn btn-primary show_task_description'><span><i class='fa fa-eye'></i></span> View</button>
                                    <?php endif; ?>
                                </td>                    
                                <td>
                                <?php if(!empty($task->task_proof_doc)): ?>
                                    <button data-task-id='<?= $task->id; ?>' data-docs='<?= $task->task_proof_doc; ?>' data-notes="<?= $task->notes; ?>" class='btn btn-primary show_task_docs' data-toggle='modal' data-target='#docs_modal' ><span><i class='fa fa-eye'></i></span> View</button>
                                <?php endif; ?>
                                </td>                    
                                <td><?= $task->task_status; ?></td>
                                <td><?= date('d M Y',strtotime($task->created_at)); ?></td>
                                <td>
                                <?php if(!empty($task->emp_requester_id)): ?>
                                    <div class='btn-group'>
                                        <button type='button' data-notes="<?= $task->notes; ?>" data-task-status='<?= $task->task_status; ?>' data-task-id='<?= $task->id; ?>' class='btn btn-success edit_task'><i class='fa fa-pencil' aria-hidden='true'></i></button>
                                    </div>
                                <?php endif; ?>
                                </td>

                            </tr>
                            <?php $records_starting_index++; ?>
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


<!-- DOCUMENT PROOF FOR TASK -->
<div id="docs_modal" class="modal fade" role="dialog"> 
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
     <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Document Proofs </h4> 
      </div>
      <div class="modal-body">
            <div class="document-proofs"></div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- TASK DOCS -->
<div id="task_description_modal" class="modal fade" role="dialog"> 
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
     <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Task Docs </h4> 
      </div>
      <div class="modal-body">
            <div class="task-description"></div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- EDIT TASK MODAL -->
<div id="task_manager_admin_edit_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit Task</h4>
        </div>
        <div class="modal-body">
            <form id="edit_task_form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('update_task_status_tm'); ?>
                <input type="hidden" name="action" value="update_task_status_tm">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="id" value="">

                <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="notes" cols="30" rows="5" class="form-control" required=""></textarea> 
                </div>

                <div class="form-group">
                    <label for="">Upload Proof Doc</label>
                    <input type="file" name="task_proof_doc[]" multiple>
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
<!-- end edit modal -->


<script>

(function($){
    $(document).ready(function(){

        $('#edit_task_form').validate({
            rules:{
                notes:{
                    alphanumeric: true,
                }
            }
        })

        $(document).on('click','.edit_task',function(e){
            var task_id =  $(this).attr('data-task-id');
            var notes =  $(this).attr('data-notes');
            var status =  $(this).attr('data-task-status');

            $('#edit_task_form input[name="id"]').val(task_id);
            $('#edit_task_form textarea[name="notes"]').val(notes);

            $("select[name='status'] > option").each(function(){
                if($(this).val()==status){
                    console.log('this is selected'+this.text);
                    $(this).attr('selected',true);
                }
                else{
                    $(this).attr('selected',false);
                }
            })

            $('#task_manager_admin_edit_modal').modal('show');

            
        });

        $(document).on('click','.show_task_docs',function(){

            let task_id=$(this).attr('data-task-id');
            let task_docs=$(this).attr('data-docs');
            let task_notes=$(this).attr('data-notes');
            console.log('task notes'+task_notes); 

            task_notes=decodeURIComponent(task_notes);

            proof_html="<p><b>Notes</b></p>";
            proof_html+=`<p>${task_notes}</p>`;

            if(task_docs!=""){
                proof_html+=`
                    <table class='table table-striped table-hover'>
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>File Url</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;

                task_docs=$.parseJSON(task_docs);
                $(task_docs).each(function(index,value){
                    proof_html+="<tr>";
                        proof_html+=`<td>${value.file_name}</td>`;
                        proof_html+=`<td><a target="_blank" href='${value.file_url}' class='btn btn-primary'><span><i class='fa fa-eye'></i></span> View</a></td>`;
                    proof_html+="</tr>";
                });

                proof_html+="</tbody>";
                proof_html+="</table>";

            }
            else{
                proof_html+="<p class='text-danger'>No Document Proof Found</p>";
            }

            $('.document-proofs').html(proof_html);

            $('#docs_modal').modal('show');


        });

        $(document).on('click','.show_task_description',function(){

            let task_docs=$(this).attr('data-docs');
            proof_html='';

            if(task_docs!=""){
                proof_html+=`
                    <table class='table table-striped table-hover'>
                        <caption>Task Files</caption>
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>File Url</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;

                task_docs=$.parseJSON(task_docs);
                $(task_docs).each(function(index,value){
                    proof_html+="<tr>";
                        proof_html+=`<td>${value.file_name}</td>`;
                        proof_html+=`<td><a target="_blank" href='${value.file_url}' class='btn btn-primary'><span><i class='fa fa-eye'></i></span> View</a></td>`;
                    proof_html+="</tr>";
                });

                proof_html+="</tbody>";
                proof_html+="</table>";

            }
            else{
                proof_html+="<p class='text-danger'>No Task Document Found</p>";
            }

            $('.task-description').html(proof_html);

            $('#task_description_modal').modal('show');


        });
    });
})(jQuery);

</script>
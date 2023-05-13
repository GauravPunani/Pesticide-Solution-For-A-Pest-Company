<?php
    if(isset($_SESSION['note_editable']['id']) && !empty($_SESSION['note_editable']['id'])){
        get_template_part('include/technician/notes/edit');
        return;
    }

    global $wpdb;

    $technician_id=(new Technician_details)->get_technician_id();

    $whereSearch="";

    if(isset($_GET['search'])){
    
        $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix.'office_notes');
        $whereSearch = (new GamFunctions)->create_search_query_string($whereSearch,trim($_GET['search']),'and');  //genereate where query string
    
    }
    else{
        $whereSearch="";
    }

    if (isset($_GET['pageno'])) {
        $pageno = $_GET['pageno'];
    } else {
        $pageno = 1;
    }
    
    
    $no_of_records_per_page =50;
    $offset = ($pageno-1) * $no_of_records_per_page; 
    
    $total_pages_sql = "
        select COUNT(*) 
        from {$wpdb->prefix}office_notes 
        where technician_id='$technician_id' 
        $whereSearch
    ";
    
    $total_rows= $wpdb->get_var($total_pages_sql);
    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $notes=$wpdb->get_results("
        select * 
        from {$wpdb->prefix}office_notes 
        where technician_id='$technician_id' 
        $whereSearch 
        order by date DESC 
        LIMIT $offset, $no_of_records_per_page
    ");
    
?>
<div class="row">
    <div class="col-sm-3">
        <form action="<?= $_SERVER['REQUEST_URI']; ?>">
            <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
            <div class="form-group">
                <label for="">Search Records</label>
                <input type="text" name="search" value="<?= @$_GET['search']; ?>" class="form-control">
            </div>
            <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
        </form>
    </div>
    <div class="col-sm-9">
        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
            <p class="alert alert-success alert-dismissible">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= strtok($_SERVER["REQUEST_URI"], '?'); ?>?view=<?= $_GET['view']; ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
            </p>
        <?php endif; ?>

    </div>
</div>


<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Note</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($notes) && count($notes)>0): ?>
                    <?php foreach($notes as $note): ?>
                        <tr>
                            <td><?= $note->client_name; ?></td>
                            <td><?= $note->note; ?></td>
                            <td><?= date('d M Y',strtotime($note->date)); ?></td>
                            <th><button data-note-id="<?= $note->id; ?>" class="btn btn-primary edit_office_note"><span><i class="fa fa-edit"></i></span> Edit</button></th>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="3">No Record Found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
    </div>
</div>

<!-- Modal -->
<div id="office_notes" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Note</h4>
      </div>
        <div class="modal-body">
            <div class="error-box"></div>
            <div class="confirmation-box">
                <form action="" id="confirmation_form">
                    <?php wp_nonce_field('insert_technician_edit_code'); ?>
                    <input type="hidden" name="action" value="insert_technician_edit_code">
                    <input type="hidden" name="type" value="note">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="name" value="<?= (new Technician_details)->get_technician_name(); ?>">
                    <p>You need permission from office by requesting a code to edit note</p>
                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                        
                </form>
            </div>
            <div class="verification-box hidden">
                <form action="" id="code_verification_form">
                    <?php wp_nonce_field('verify_technician_edit_code'); ?>
                    <input type="hidden" name="action" value="verify_technician_edit_code">
                    <input type="hidden" name="type" value="note">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="db_id" value="">
                    <input type="hidden" name="name" value="<?= (new Technician_details)->get_technician_name(); ?>">
                    <div class="form-group">
                            <label for="">Please enter the verification code</label>
                            <input type="text" name="code" maxlength="6" class="form-control">
                    </div>
                    <button id="verification_submit_btn" class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Verify</button>
                </form>
            </div>
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
            $('.edit_office_note').on('click',function(){
                let note_id=$(this).attr('data-note-id');

                $('input[name="id"]').val(note_id);

                $('#office_notes').modal('show');
            })
        })
    })(jQuery);
</script>
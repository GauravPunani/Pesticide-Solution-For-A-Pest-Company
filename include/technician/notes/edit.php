<?php
global $wpdb;


$note=$wpdb->get_row("select * from {$wpdb->prefix}office_notes where id='{$_SESSION['note_editable']['id']}' ");
?>

<form id="office_notes" class="res-form" action="<?= admin_url('admin-post.php'); ?>" method="post">
    <h2 class="text-center form-head">Edit Notes</h2>        

    <input type="hidden" name="action" value="update_office_notes">
    <input type="hidden" name="note_id" value="<?= $_SESSION['note_editable']['id']; ?>">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="type" value="invoice">
    <div class="form-group">
        <label for="">Client Name</label>
        <input type="text" class="form-control" value="<?= $note->client_name; ?>" name="client_name" required>
    </div>
    <div class="form-group">
        <label for="">Notes</label>
        <textarea name="note"  cols="30" rows="5" class="form-control" required><?= $note->note; ?></textarea>
    </div>
    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Note</button>
</form>

<script>

(function($){
    $(document).ready(function(){
        $('#office_notes').validate({
            rules:{
                client_name:"required",
                note:"required",
            }
        })
    })
})(jQuery);

</script>

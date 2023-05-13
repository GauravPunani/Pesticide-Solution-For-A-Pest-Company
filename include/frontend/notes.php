<div class="container ">
    <div class="row">
        <div class="col-sm-12">
            <form id="office_notes" class="res-form" action="<?= admin_url('admin-post.php'); ?>" method="post">
                <a id="reset_invoice_page"><i class="fa fa-arrow-left" aria-hidden="true"></i> Restart the page </a>            
                <h2 class="form-head">Office Notes</h2>        

                <input type="hidden" name="action" value="office_notes">
                <input type="hidden" name="type" value="invoice">
                <div class="form-group">
                    <label for="">Client Name</label>
                    <input type="text" class="form-control" name="client_name" required>
                </div>
                <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="note"  cols="30" rows="5" class="form-control" required></textarea>
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit</button>
            </form>
        </div>
    </div>
</div>

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
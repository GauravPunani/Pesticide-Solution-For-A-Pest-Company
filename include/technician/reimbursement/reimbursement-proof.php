<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="text-center"><?php (new GamFunctions)->getFlashMessage(); ?></div>
                <form class="res-form" id="reimbursement_form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                    <h2 class="text-center form-head">Reimbursement</h2>

                    <input type="hidden" name="action" value="reimbursement_proof">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="form-group">
                        <label for="">Reimbursement Amount</label>
                        <input type="text" class="form-control numberonly" name="amount">
                    </div>
                    <div class="form-group">
                        <label for="">Reimbursement Proof</label>
                        <input type="file" class="form-control" accept="image/*" name="docs">
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit</button>
                </form>

            </div>
        </div>    
    </div>
</div>
<script>

(function($){
    $(document).ready(function(){

        $.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        }, 'File size must be less than 10MB');        
        
        $('#reimbursement_form').validate({
            rules:{
                amount:"required",
                docs:{
                    required: true,
                    filesize: 10000000,
                }
            }
        })
    });

})(jQuery);
</script>
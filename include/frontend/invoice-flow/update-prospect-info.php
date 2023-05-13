<div class="container res-form">
    <div class="row">        
        <div class="col-sm-12">          
            <div class="row">
                <div class="col-sm-12">
                    <button type="button" class="btn btn-danger btn-sm pull-right" id="reset_invoice_page"><span><i class="fa fa-refresh"></i></span> Restart the page</button>
                </div>
            </div>            
            <form method="post" id="updateProspectStatusForm" class="reset-form" action="<?= admin_url('admin-post.php'); ?>">

                <?php wp_nonce_field('update_recurring_prospect_status'); ?>
                <input type="hidden" name="action" value="update_recurring_prospect_status">

                <h3 class="page-header text-center">Update Prospect Status</h3>

                <div class="form-group">
                    <label for="">Degree of interest</label>
                    <select name="status" class="form-control select2-field">
                        <option value="">Select</option>
                        <option value="interested">Interested</option>
                        <option value="semi_interested">Semi Interested</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Notes / Comments</label>
                    <textarea type="text" cols="5" rows="5" class="form-control" name="notes"></textarea>
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Prospect Info</button>

            </form>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#updateProspectStatusForm').validate({
                rules: {
                    status: "required",
                    notes: "required"
                }
            })
        });
    })(jQuery);
</script>
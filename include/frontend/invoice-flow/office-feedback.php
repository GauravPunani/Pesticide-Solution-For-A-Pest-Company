<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <form id="officeNotesForm" action="<?= admin_url('admin-post.php'); ?>" class="res-form" method="post" enctype="multipart/form-data">

                <?php wp_nonce_field('invoice_flow_office_notes'); ?>
                <input type="hidden" name="action" value="invoice_flow_office_notes">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <h3 class="page-header text-center">Office Feedback</h3>

                <!-- CLIENT SATISFACTION QUESTION  -->
                <div class="form-group">
                    <label for="">Is client satisfied with service ?</label>
                    <select name="client_satisfied" class="form-control">
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <div class="form-gorup hidden disatisfaction_reason">
                    <label for="">Please explain reason for dissatisfaction</label>
                    <textarea name="disatisfaction_reason" cols="30" rows="5" class="form-control"></textarea>
                </div>       

                <div class="form-group">
                    <label for="">Notes / Comment <small>(These notes will appear on google calendar event as well.)</small></label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label for="">Images (optional)</label>
                    <input accept="image/*" type="file" class="form-control" name="optional_images[]" multiple>
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Submit Notes</button>

            </form>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){

            $('select[name="client_satisfied"]').on('change', function(){
                console.log('in method');
                const status = $(this).val();
                console.log('status' + status);
                if(status === "no"){
                    $('.disatisfaction_reason').removeClass('hidden');
                }
                else{
                    $('.disatisfaction_reason').addClass('hidden');
                }
            });

            $('#officeNotesForm').validate({
                rules: {
                    client_satisfied: "required",
                    disatisfaction_reason: "required",
                    notes: "required",

                }
            });
        });
    })(jQuery);
</script>
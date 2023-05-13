<div id="codeVerification" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Maintenance Amount Verification</h4>
            </div>

            <div class="modal-body">
                <div class="error-box"></div>
                
                <div class="confirmation-box">
                    <form id="generateCodeForm">
                        <?php wp_nonce_field('code_module_generate_code'); ?>

                        <p>You need permission from office in order to generate maintenance contract of amount $59 or less.</p>                        

                        <input type="hidden" name="action" value="code_module_generate_code">
                        <input type="hidden" name="type" value="maintenance_amount_verification">
                        <button id="generateCodeFormSubmitBtn" class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Request Code</button>
                    </form>
                </div>

                <div class="verification-box hidden">
                    <form id="verifyCodeForm">

                        <?php wp_nonce_field('code_module_verify_code'); ?>

                        <p>Please ask the code from office and insert below.</p>

                        <input type="hidden" name="action" value="code_module_verify_code">
                        <input type="hidden" name="db_id">                        

                        <div class="form-group">
                            <label for="">Please enter the verification code</label>
                            <input type="text" name="code" maxlength="6" class="form-control">
                        </div>

                        <button id="verification_submit_btn" class="btn btn-primary">Verify & Submit</button>
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

    let db_id;
    let isOtpVerified = false;

    (function($){
        $(document).ready(function(){

            $('#generateCodeForm').on('submit', function(e){
                e.preventDefault();

                jQuery.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php') ?>",
                    data: $(this).serialize(),
                    dataType: "json",
                    beforeSend: function(){
                        $('#generateCodeFormSubmitBtn').attr('disabled', true);
                    },
                    success: function(data){
                        if(data.status === "success"){
                            $('.confirmation-box').hide();

                            // set the code db id as well for verification
                            $('.verification-box').removeClass('hidden');

                            // set the last insert id form 
                            db_id = data.data.db_id;
                            $('#verifyCodeForm input[name="db_id"]').val(data.data.db_id);                    
                        }
                    }
                });     

            });

            $('#verifyCodeForm').on('submit', function(e){
                e.preventDefault();

                jQuery.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data: jQuery(this).serialize(),
                    dataType: 'json',
                    beforeSend: function(){
                        jQuery('.error-box').html("");
                        jQuery('#verification_submit_btn').attr('disabled',true);
                        jQuery('#verification_submit_btn').text('Verifying....');                
                    },
                    success: function(data){
                        if(data.status === "success"){
                            isOtpVerified = true;
                            const db_code = jQuery('#verifyCodeForm input[name="code"]').val();
                            jQuery('.maintenance-forms input[name=db_id]').val(db_id);
                            jQuery('.maintenance-forms input[name=db_code]').val(db_code);

                            jQuery('.maintenance-forms').submit();
                        }
                        else{
                            // display the error 
                            console.log('code did not matched');
                            $('.error-box').html("<p class='text-danger'>Verification code did't matched, try again with correct code</p>");
                            $('#verification_submit_btn').text('Verify & submit').attr('disabled',false);
                            
                        }
                    }
                });

            });

            $('#verifyCodeForm').validate({
                rules: {
                    code: "required"
                }
            })

        })
    })(jQuery);
</script>
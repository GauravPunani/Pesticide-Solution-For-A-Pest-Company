<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <form id="maintenance_step_form" action="<?= admin_url('admin-post.php'); ?>" class="res-form reset-form" method="post">
                <?php (new GamFunctions)->getFlashMessage(); ?>

                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-danger btn-sm pull-right" id="reset_invoice_page"><span><i class="fa fa-refresh"></i></span> Restart the page</button> 
                    </div>
                </div>                
                
                <h3 class="page-header text-center">Maintenance Contract</h3>

                <?php wp_nonce_field('invoice_flow_maintenance_step'); ?>

                <input type="hidden" name="action" value="invoice_flow_maintenance_step">
                <input type="hidden" name="type" value="invoice_maintenance_step">

                <!-- EVENT ERROR PLACEHOLDER  -->
                <div class="event_error"></div>

                <!-- STEP 2 - IF NOT RECURRING THEN ASK FOR CLIENT REJECT OR ACCEPT  -->
                <div class="step-2">
                    <div class="form-group">
                        <label for="">Client interested in maintenance plan ?</label>
                        <select name="client_interested" class="form-control select2-field">
                            <option value="">Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                </div>

                <!-- STEP 4 - IF MAINNTENACE OFFER SELECTED , THEN SHOW TYPES OF MAINTNENACE  -->
                <div class="step-4 hidden">
                    <!-- TYPE OF MAINTENANCE PLAN  -->
                    <div class="form-group maintenance_plan">
                        <label for="">Select type of maintenance plan.</label>
                        <select name="maintenane_type" class="form-control select2-field">
                            <option value="">Select</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="special">Special</option>
                            <option value="commercial">Commercial</option>
                        </select>
                    </div>  
                </div>

                <button class="btn btn-primary sendform"><span><i class="fa fa-paper-plane"></i></span> Submit & Continue</button>
            </form>
        </div>
    </div>
</div>

<script>

(function($){
    $(document).ready(function(){

        $('select[name="client_interested"]').on('change',function(){

            const tech_response = $(this).val();

            if(tech_response === "yes"){
                $('.step-4').removeClass('hidden');
                // make signature required
                change_form_action();
            }
            else if(tech_response === "no"){
                $('.step-4').addClass('hidden');

                // make signature required
                change_form_action();
            }
            else if(tech_response === "already-on-maintenance"){
                $('.step-4').addClass('hidden');

                // make signature required
                change_form_action();
            }
        });

        $('#maintenance_step_form').validate({
            rules:{
                client_interested:"required",
                maintenane_type:"required",
            }
        });

        $('#confirmation_form').on('submit',function(e){
            e.preventDefault();
            $.ajax({
                type:"post",
                url:'<?= admin_url('admin-ajax.php'); ?>',
                dataType:"json",
                data:$(this).serialize(),
                beforeSend:function(){
                    // show the progress 
                    $('#confirm_submit_btn').text('Requesting...please wait'); 
                },
                success:function(data){
                    if(data.status=="success"){
                        // show the code box for confirmation 
                        $('.confirmation-box').hide();

                        // set the code db id as well for verification
                        $('.verification-box').removeClass('hidden');

                        // set the last insert id form 
                        $('input[name="db_id"]').val(data.db_id);
                    }
                }

            })
        });

        $('#code_verification_form').on('submit',function(e){
            e.preventDefault();
            $.ajax({
                type:"post",
                url:'<?= admin_url('admin-ajax.php'); ?>',
                data:$(this).serialize(),
                dataType:"json",
                beforeSend:function(){
                    $('.error-box').html("");
                    $('#verification_submit_btn').attr('disabled',true);
                    $('#verification_submit_btn').text('Verifying....');
                    
                },
                success:function(data){
                    if(data.status=="success"){
                        console.log('code matched');
                        location.reload();
                        // set the session and show the edit form
                    }
                    else{
                        // display the error 
                        console.log('code did not matched');
                        $('.error-box').html(`<p class='text-danger'>${data.message}</p>`);
                        $('#verification_submit_btn').text('Verify & submit').attr('disabled',false);
                        
                    }
                }
            })
        });

    })
})(jQuery);

function change_form_action(){

    $('#maintenance_step_form input[name="action"]').val('invoice_flow_maintenance_step');
    $('.sendform').text('Submit & Continue to Maintenance Contract');    

    // enable the form submit button 
    $('.sendform').attr('disabled',false);
}
</script>
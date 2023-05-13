<?php

global $wpdb;

$cold_caller_id = (new ColdCaller)->getLoggedInColdCallerId();
$cold_caller = (new ColdCaller)->getColdCallerById($cold_caller_id);

if(!empty($_SESSION['caller_editable']) && $_SESSION['caller_editable']['id'] == $cold_caller_id){
    return get_template_part('template-parts/cold-caller/edit-profile', null, ['cold_caller_id' => $cold_caller_id]);
}
?>
<p class="text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#codeverification"><span><i class="fa fa-edit"></i></span> Edit Profile</button></p>

<!-- CODE VERIFICATION MODAL -->
<div id="codeverification" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit Cold Caller</h4>
        </div>
        <div class="modal-body">
            <div class="error-box"></div>
            <div class="confirmation-box">
                <form action="" id="confirmation_form">
                    <input type="hidden" name="action" value="insert_cold_caller_edit_code">
                    <input type="hidden" name="type" value="caller">
                    <input type="hidden" name="id" value="<?= $cold_caller_id; ?>">
                    <input type="hidden" name="name" value="<?= $cold_caller->name; ?>">
                    <p>You need permission from office by requesting a code to edit cold caller</p>
                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
                        
                </form>
            </div>
            <div class="verification-box hidden">
                <form action="" id="code_verification_form">
                    <input type="hidden" name="action" value="verify_cold_caller_edit_code">
                    <input type="hidden" name="type" value="caller">
                    <input type="hidden" name="id" value="<?= $cold_caller_id; ?>">
                    <input type="hidden" name="db_id" value="">
                    <input type="hidden" name="name" value="<?= $cold_caller->name; ?>">
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

<?php get_template_part('include/admin/cold-caller/view-profile', null, ['cold_caller_id' => $cold_caller_id]); ?>
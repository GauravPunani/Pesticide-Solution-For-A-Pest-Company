<?php

global $wpdb;

$email_id = $_GET['edit-id'];
// $email_id=$args['edit-id'];

$email=$wpdb->get_row("select * from {$wpdb->prefix}emails where id='$email_id'");

?>

<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <?php if($email): ?>
                        <h3 class="page-header">Edit Email Record</h3>
                        <form method="post" id="edit_email_form" action="<?= admin_url('admin-post.php') ?>">
							<?php wp_nonce_field('edit_email_record'); ?>
                            <input type="hidden" name="action" value="edit_email_record">
                            <input type="hidden" name="email_id" value="<?= $email->id; ?>">
                            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                            <!-- <p>Email : <b>< ?= $email->email; ?></b></p> -->
                            <div class="form-group">
                                <label for="">Email</label>
                                <input type="text" name="email" class="form-control" value="<?= $email->email; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control" value="<?= $email->name; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Address</label>
                                <input type="text" name="address" class="form-control" value="<?= $email->address; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Phone No.</label>
                                <input type="text" name="phone" class="form-control" value="<?= $email->phone; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Type</label>
                                <select name="status" id = "ddlPassport" onchange = "ShowHideDiv()" class="form-control select2-field toggle-btn">
                                    <option value="<?= $email->status; ?>"><?= $email->status; ?></option>
                                    <option value="cold_calls" onclick="myFunction()"  id="myelement">Cold Calls</option>
                                    <option value="non_reocurring" <?= $email->status=="non_reocurring" ? 'selected' : ''; ?> class="show-btn">Non-reocurring</option>
                                    <option value="reocurring"<?= $email->status=="reocurring" ? 'selected' : ''; ?>>Reocurring</option>
                                </select>
                            </div>


                             
                            <div class="form-group another-element" id="myDIV">
                                <div id="dvPassport" style="display: none">
                                    <hr>
                                        <label for="">Book Appointment</label>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" id="radio1" name="book_appointment" value="1" checked>
                                            <label class="form-check-label" for="radio1"> Approved</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" id="radio2" name="book_appointment" value="0">
                                            <label class="form-check-label" for="radio2"> Not Approved</label>
                                        </div>
                                        <div class="form-group"></div>
                                        <!-- Cold-Call Note -->
                                        <div class="form-group" id="atextarea">
                                            <label for="review_link">Enter the Notes : </label>
                                            <textarea class="form-control" rows="4" cols="50" name="note" id="note"></textarea>
                                        </div>
                                    <hr> 
                                </div>
                            </div>
                           
                            
                            <div class="form-group">
                                <label for="">Email Valid</label>
                                <select name="is_client_email_valid" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <option value="yes" <?= $email->is_valid=="yes" ? 'selected' : ''; ?>>Yes</option>
                                    <option value="no" <?= $email->is_valid=="no" ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Email</button>
                        </form>
                    <?php else: ?>
                        <h3 class="text-danger text-center">No Record Found</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<script>

    function ShowHideDiv() {
        var ddlPassport = document.getElementById("ddlPassport");
        var dvPassport = document.getElementById("dvPassport");
        dvPassport.style.display = ddlPassport.value == "cold_calls" ? "block" : "none";
    }



 (function($){
        $(document).ready(function(){
            $('#edit_email_form').validate({
                rules:{
                    type:"required",
                    is_client_email_valid:"required"
                },
                messages:{
                    email:{
                        remote:"Email already exists"
                    }
                }
            })
        })
    })(jQuery);
</script>

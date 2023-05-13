<?php

$role = (array_key_exists('role', $args) && !empty($args['role'])) ? $args['role'] : '';
?>

<div id="proof-of-payment" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Proof Of Payment</h4>
            </div>

            <div class="modal-body">
                    <form id="uploadPaymentProofForm" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

                        <?php wp_nonce_field('upload_proof_of_payment'); ?>
                        <input type="hidden" name="action" value="upload_proof_of_payment">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <input type="hidden" name="payment_id" value="">

                        <?php if($role != "office_staff" && $role != "cold_caller"): ?>
                            <p>Payble Amount : <span id="payble_amount"></span></p>                            
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="">Amount Paid</label>
                            <input type="text" class="form-control" name="amount_paid" required>
                        </div>

                        <div class="form-group">
                            <label for="">Payment Description (optional)</label>
                            <textarea name="payment_description" cols="30" rows="5" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Payment Proof</label>
                            <input type="file" name="payment_proof[]" class="form-control" required multiple/>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Payment Proof</button>
                    </form>
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
            $('#uploadPaymentProofForm').validate({
                rules: {
                    amount_paid: "required",
                    "payment_proof[]": "required",
                }
            });
        })
    })(jQuery);
</script>
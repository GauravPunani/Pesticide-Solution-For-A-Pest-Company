<div id="smsContractModal" class="modal fade" rold="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <p>SMS Contract Link To Client</p>
            </div>
            <div class="modal-body">
                <form id="smsContractForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                    
                    <?php wp_nonce_field('sms_maintenance_contract_link'); ?>

                    <input type="hidden" name="action" value="sms_maintenance_contract_link">
                    
                    <input type="hidden" name="contract_type">
                    <input type="hidden" name="contract_id" value="">

                    <div class="form-group">
                        <label for="">Client Phone No.</label>
                        <input type="text" class="form-control" name="phone_no" value="" placeholder="e.g. +1123-456-7890" required >
                    </div>

                    <button id="sms_contract_btn" class="btn btn-primary"><span><i class="fa fa-envelope"></i></span> SMS Contract Link</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#smsContractForm').validate({
                rules: {
                    phone_no: "required"                   
                },
                submitHandler: function(form){
                    jQuery.ajax({
                        type: "post",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: $('#smsContractForm').serialize(),
                        dataType: "json",
                        beforeSend: function(){
                            $('#sms_contract_btn').attr('disabled', true);
                        },
                        success: function(data){
                            alert(data.message);
                            $('#sms_contract_btn').attr('disabled', false);
                            $('#smsContractModal').modal('hide');
                        }
                    })
                }
            });            
        })
    })(jQuery);
</script>
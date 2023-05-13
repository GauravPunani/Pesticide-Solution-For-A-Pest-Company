<?php
    /* Template Name: daily-proof-of-deposit */
    get_header();
?>
<div class="row">
    <div class="col-md-offset-3 col-md-6">
        <form action="<?= admin_url('admin-post.php'); ?>" id="daily-deposit-form" method="post" enctype="multipart/form-data">
            <h3 class="text-center">Daily Deposit Proof</h3>
            <input type="hidden" name="action" value="daily_deposit_proof">
            <div class="error-box text-danger"></div>
            <div class="form-group">
                <label for="">Date</label>
                <input class="form-control" type="date" value="<?= date('Y-m-d'); ?>" name="deposit_date" required>
            </div>

            <div class="form-group">
                <label for="">Total Amount Deposited</label>
                <input type="text" class="form-control numberonly" name="total_amount" required>
            </div>
                
            <div class="form-group">
                <label for="">Please Upload Proof Of Deposit</label>
                <input type="file" class="form-control" name="desposit_proof[]"  multiple required>
            </div>

            <div class="form-group">
                <p>is there a discrepancy in money that you need to report ?</p>
                <label for="Yes">Yes</label>
                <input type="radio" id="yes-select" name="discrepancy_yes" value="discrepancy_yes">
                <label for="No">No</label>
                <input type="radio" id="no-select" name="discrepancy_yes" value="discrepancy_no">
            </div>
            <div class="dscrepancy_section" style="display: none;">
                <div class="form-group">
                <label for="">Discrepancy Amount</label>
                <input type="text" class="form-control numberonly" name="dscrepancy_amount" >
            </div>
            <div class="form-group">
                <label for="">Describe Discrepancy</label>
                <textarea class="form-control" name="describe_discrepancy" ></textarea>
                
            </div>
            <div class="form-group">
                <label for="">Please Upload Proof Of Discrepancy</label>
                <input type="file" class="form-control" name="dscrepancy_proof[]"  multiple>
            </div>
            </div>
            <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="submit_btn">Submit</span></button>

        </form>
    </div>
</div>
<script type="text/javascript">
    (function($){
        $(document).ready(function(){
            $("#yes-select").click(function(){
                $(".dscrepancy_section").show();
                });
            $("#no-select").click(function(){
                $(".dscrepancy_section").hide();
            });
        })
    })(jQuery);
</script>
<script>    
    var ajax_url="<?= admin_url('admin-ajax.php'); ?>";
</script>
<script src="<?= get_template_directory_uri()."/assets/js/daily-proof-of-deposit.js" ?>"></script>
<?php

get_footer();
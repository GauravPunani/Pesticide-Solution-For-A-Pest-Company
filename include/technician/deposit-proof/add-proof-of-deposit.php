<form class="res-form" id="daily-deposit-form" method="post" action="<?= admin_url('admin-post.php');?>" enctype="multipart/form-data">
    <h3 class="text-center">Daily Deposit Proof</h3>
    <div class="text-center"><?php (new GamFunctions)->getFlashMessage(); ?></div>
    <?php wp_nonce_field('daily_deposit_proof'); ?>
    <input type="hidden" name="action" value="daily_deposit_proof">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI'];?>">

    <div class="form-group">
        <label for="">Date</label>
        <input class="form-control" type="date" value="<?= date('Y-m-d'); ?>" name="deposit_date" >
    </div>

    <div class="form-group">
        <label for="">Total Amount Deposited</label>
        <input type="text" class="form-control numberonly" name="total_amount" >
    </div>
        
    <div class="form-group">
        <label for="">Please Upload Proof Of Deposit</label>
        <input type="file" class="form-control" name="desposit_proof" accept="image/*" multiple="multiple">
    </div>

    <div class="form-group">
        <p>is there a discrepancy in money that you need to report ?</p>
        <label for="Yes">Yes</label>
        <input type="radio" id="yes-select" name="any_discrepancy" value="yes">
        <label for="No">No</label>
        <input type="radio" id="no-select" name="any_discrepancy" value="no">
    </div>

    <div class="dscrepancy_section hidden">
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
            <input type="file" class="form-control" name="dscrepancy_proof" accept="image/*" multiple="multiple">
        </div>
    </div>

    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> submit</button>

</form>
<script type="text/javascript">
    (function($){
        $(document).ready(function(){

            $('input[name="any_discrepancy"]').on('click', function(){
                const any_discrepancy = $(this).val();
                if(any_discrepancy == "no"){
                    $('.dscrepancy_section').addClass('hidden');
                }
                else{
                    $('.dscrepancy_section').removeClass('hidden');                    
                }
            })

            $("#yes-select").click(function(){
                $(".dscrepancy_section").show();
            });
            $("#no-select").click(function(){
                $(".dscrepancy_section").hide();
            });


            $.validator.addMethod('filesize', function (value, element, param) {
                return this.optional(element) || (element.files[0].size <= param)
            }, 'File size must be less than 5MB');

            $('#daily-deposit-form').validate({
                rules: {
                    deposit_date: {
                        required: true,
                        remote:{
                            url: "<?= admin_url('admin-ajax.php'); ?>",
                            data:{
                                action : "check_daily_deposit_exist_or_not",
                                "_wpnonce": "<?= wp_create_nonce('check_daily_deposit_exist_or_not') ?>",
                            },
                            type: "post"
                        },
                    },
                    total_amount: "required",
                    desposit_proof:{
                        required: true,
                        filesize: 10000000,
                    },
                    any_discrepancy: "required",
                    dscrepancy_amount: "required",
                    describe_discrepancy: "required",
                    dscrepancy_proof:{
                        required: true,
                        filesize: 10000000,
                    },
                },
                messages: {
                    deposit_date: {
                        required: "Desposit date is required",
                        remote: "Current date proof of deposit is already submitted"
                    }
                }                
            });

        })
    })(jQuery);   

</script>
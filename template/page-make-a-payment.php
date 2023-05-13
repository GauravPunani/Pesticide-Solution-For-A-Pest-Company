<?php
/* Template Name: Make a Payment */
get_header();
global $post;
?>

<?php if(!post_password_required($post)): ?>
    <section id="content">
        <div class="container">
            <div class="row">
                <div class="col-md-offset-3 col-md-6 res-form">
                    <h2 class="form-head text-center">Make A Payment</h1>
                    <form action="" id="client_payment_form">
                        <?php wp_nonce_field('process_payment'); ?>
                        <input type="hidden" name="action" value="process_payment">
                        <input type="hidden" name="process_type" value="manual_payment">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="">Select Invoice</label>
                                <select name="invoice_id" class="form-group invoice_dropdown" required></select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Cardholder Name</label>
                                <input type="text" class="form-control" name="cardholder_name">
                            </div>
                        </div>                        

                        <div class="col-md-4">
                            <div class="form-group" id="card-number-field">
                                <label for="cardNumber">Card Number</label>
                                <input type="text" name="card_no" maxlength="16" class="form-control numberonly" id="cardNumber">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Expiry Date</label> <small>e.g. 01/23</small>
                                <input type="text" maxlength="5" class="form-control expiry_date" name="expiry_date">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" maxlength="4" name="card_cvv" class="form-control numberonly" id="cvv">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="payble_amount"></div>   
                        </div>
                        
                        <div class="col-sm-12">
                            <div class="form-group errors">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group" id="pay-now">
                                <button type="submit" class="btn btn-primary btn-block" id="confirm-purchase">Pay</button>
                            </div>
                        </div>    

                    </form>

                </div>
            </div>
        </div>
    </section>

    <script>
        var allow_type=true;
        var site_url="<?= site_url(); ?>";

        (function($){
            $(document).ready(function(){
                $('#client_payment_form').validate({
                    rules:{
                        invoice_id:"required",
                        cardholder_name:"required",
                        card_no : {
                            required : true,
                            maxlength : 16
                        },
                        expiry_date :{
                            required : true,
                            maxlength : 5
                        },
                        card_cvv : {
                            required :true,
                            maxlength : 4
                        },
                    },
                    submitHandler:function (form){

                        $.ajax({
                            type:'post',
                            url:"<?= admin_url('admin-ajax.php'); ?>",
                            data:$('#client_payment_form').serialize(),
                            dataType:"json",
                            beforeSend:function(){
                                $('.errors').html('');
                                $('#confirm-purchase').html('<div class="loader"></div>');
                                $('#confirm-purchase').attr('disabled',true);
                            },
                            success:function(data){
                                // console.log(data);
                                // return;
                                if(data.status=="success"){
                                    $('.errors').html('<p class="text-success">Payment Recieved Successfully, Redirecting to recipet page...</p>');
                                    window.location.replace(`${site_url}/tekcardpayment/?transaction_id=${data.data.transaction_id}`);
                                }
                                else{
                                    console.log('in else part');
                                    $('.errors').html(`<p class='text-danger'>${data.message}</p>`);
                                    $('#confirm-purchase').html('Pay');
                                    $('#confirm-purchase').attr('disabled',false);
                                }
                            }
                        });

                        return false;
                    }
                });
                
                $('.invoice_dropdown').select2({
                    width: '100%',
                    minimumInputLength:4,
                    ajax: {
                        url: '<?= admin_url('admin-ajax.php') ?>',
                        dataType: 'json',
                        method:"post",
                        data: function (params) {
                            var query = {
                                search: params.term,
                                type: 'public',
                                type: 'invoice_id',
                                action:"search_invoice_by_ajax"
                            }

                            // Query parameters will be ?search=[term]&type=public
                            return query;
                        }                    
                        // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
                    }
                });
                
                $('.expiry_date').keyup(function(e){
                    if(e.keyCode!=8 && $(this).val().length==2){
                        allow_type=false;
                        let current_string=$(this).val();
                        let new_string=current_string+"/";
                        $('.expiry_date').val(new_string);
                        allow_type=true;
                    }
                    if($(this).val().length==5){
                        console.log('length matched');
                        setTimeout(function(){
                            $('input[name="card_cvv"]').focus();
                        }, 1);
                    }
                });

                $('.expiry_date').keydown(function() {
                //code to not allow any changes to be made to input field
                return allow_type;
                });
                
                $('.invoice_dropdown').on('change', function() {
                    var invoice_id = $(".invoice_dropdown option:selected").val();
                    $.ajax({
                        type:"post",
                        url:"<?= admin_url('admin-ajax.php'); ?>",
                        data:{
                            action:"get_invoice_amount",
                            invoice_id:invoice_id
                        },
                        dataType:'json',
                        beforeSend:function(){
                            $('.payble_amount').html('<div class="loader"></div>');
                        },
                        success:function(data){
                            if(data.status=="success"){
                                $('.payble_amount').html(`<p>Total Payble Amount : <b>$${data.data.payble_amount}</b>`);
                                $('#confirm-purchase').html(`Pay $${data.data.payble_amount}`);
                            }
                        }
                    })
                });
                            

            });
        })(jQuery);
    </script>
<?php else:  ?>
    <?= get_the_password_form(); ?>
<?php endif; ?>

<?php
get_footer();

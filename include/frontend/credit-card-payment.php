<?php
global $wpdb;
$invoice_id = $_SESSION['invoice-data']['invoice_id'];
$invoice_data = (new Invoice)->getInvoiceById($invoice_id);
?>

<div class="creditCardForm">

    <form id="payment_form" action="<?= admin_url('admin-post.php'); ?>" class="res-form reset-form" method="post">
        <?php wp_nonce_field('process_payment'); ?>
        <input type="hidden" name="action" value="process_payment">
        <input type="hidden" name="process_type" value="invoice_flow">

        <div class="row">
            <div class="col-sm-12">
            <button type="button" class="btn btn-danger btn-sm pull-right" id="reset_invoice_page"><span><i class="fa fa-refresh"></i></span> Restart the page</button> 
            </div>
        </div>
        <div class="row">

            <h3 class="text-center">Process Payment</h3>

            <table class="table table-striped table-hover">
                <tr>
                    <th>Name</th>
                    <td><?= $invoice_data->client_name; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= $invoice_data->email; ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?= $invoice_data->address; ?></td>
                </tr>
                <tr>
                    <th>Amount to be charged</th>
                    <td><?= (new GamFunctions)->beautify_amount_field($invoice_data->total_amount); ?></td>
                </tr>
            </table>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="">Cardholder Name</label>
                    <input type="text" class="form-control" name="cardholder_name" value="<?= $invoice_data->client_name; ?>">
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
                <div class="form-group errors">
                </div>
            </div>

            <div class="col-sm-12">
                <div class="form-group" id="pay-now">
                    <button type="submit" class="btn btn-primary btn-block" id="confirm-purchase">Pay <?= (new GamFunctions)->beautify_amount_field($invoice_data->total_amount); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
var site_url="<?= site_url(); ?>";
var payble_amount="<?= (new GamFunctions)->beautify_amount_field($invoice_data->total_amount); ?>";
let allow_type=true;
    (function($){
        $(document).ready(function(){

            $('input[name="card_no"]').keyup(function(){
                if($(this).val().length==16){
                    setTimeout(function(){
                        $('input[name="expiry_date"]').focus();
                    }, 1);
                }

            })

            $('.expiry_date').keyup(function(e){
                if(e.keyCode!=8 && $(this).val().length==2){
                    allow_type=false;
                    let current_string=$(this).val();
                    let new_string=current_string+"/";
                    $('.expiry_date').val(new_string);
                    allow_type=true;
                }
                if($(this).val().length==5){
                    setTimeout(function(){
                        $('input[name="card_cvv"]').focus();
                    }, 1);
                }

            });

            $('.expiry_date').keydown(function() {
            //code to not allow any changes to be made to input field
            return allow_type;
            });        

            $('#payment_form').validate({
                rules:{
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
                    cardholder_name:"required"
                },
                submitHandler:function (form){

                    $.ajax({
                        type:'post',
                        url:"<?= admin_url('admin-ajax.php'); ?>",
                        data:$('#payment_form').serialize(),
                        dataType:"json",
                        beforeSend:function(){
                            $('.errors').html('');
                            $('#confirm-purchase').html('<div class="loader"></div>');
                            $('#confirm-purchase').attr('disabled',true);
                        },
                        success:function(data){
                            if(data.status=="success"){
                                $('.errors').html('<p class="text-success">Payment Recieved Successfully</p>');
                                // window.location.replace(`${site_url}/tekcardpayment/?transaction_id=${data.data.transaction_id}`);
                                location.reload();
                            }
                            else{
                                $('.errors').html(`<p class='text-danger'>${data.message}</p>`);
                                $('#confirm-purchase').html('Pay'+payble_amount);
                                $('#confirm-purchase').attr('disabled',false);
                            }
                        }
                    });

                    return false;
                }
            });
        });
    })(jQuery);
</script>
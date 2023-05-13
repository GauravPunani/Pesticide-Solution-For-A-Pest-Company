<section id="content">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-3 col-md-6 res-form">
                <h2 class="form-head text-center">Make A Payment</h1>
                <form action="" id="client_payment_form">
				<?php wp_nonce_field('make_a_payment'); ?>
                    <input type="hidden" name="action" value="client_self_payment">
                    <div class="form-group">
                        <label for="">Location <span class="text-danger">*</span></label>
                        <select name="client_location"  class="form-control">
                            <?php $locations=(new GamFunctions)->get_all_locations(); ?>
                            <?php if(is_array($locations) && count($locations)>0): ?>
                                    <?php foreach($locations as $key=>$val): ?>
                                            <option value="<?= $val->slug; ?>" ><?= $val->location_name; ?></option>
                                    <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="client_name" required>
                    </div>
                    <div class="form-group">
                        <label for="">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="client_email" required>
                    </div>
                    <div class="form-group">
                        <label for="">Phone No.</label>
                        <input type="text" class="form-control" name="client_phone_no">
                    </div>
                    <div class="form-group">
                        <label for="">Address</label>
                        <textarea name="client_address" id="" cols="30" rows="5" class="form-control"></textarea>
                    </div>

                    <!-- payment fields  -->
                    <div class="col-sm-12">
                        <div class="form-group">
                                <label for="owner">Name on Card <span class="text-danger">*</span></label>
                                <input type="text" name="card_owner" class="form-control" id="owner">
                        </div>                    
                    </div>
                    <div class="col-sm-6">
                            <div class="form-group" id="card-number-field">
                                <label for="cardNumber">Card Number <span class="text-danger">*</span></label>
                                <input type="text" name="card_no" class="form-control numberonly" id="cardNumber">
                            </div>
                    </div>
                    <div class="col-sm-6">
                            <div class="form-group">
                                <label for="cvv">CVV <span class="text-danger">*</span></label>
                                <input type="text" name="card_cvv" class="form-control numberonly" id="cvv">
                            </div>
                    </div>



                    <div class="col-sm-6">
                            <div class="form-group" id="expiration-date">
                                <label>Expiration Date <span class="text-danger">*</span></label>
                                <select name="card_month" class="form-control" id="card_month">
                                    <option value="01">January</option>
                                    <option value="02">February </option>
                                    <option value="03">March</option>
                                    <option value="04">April</option>
                                    <option value="05">May</option>
                                    <option value="06">June</option>
                                    <option value="07">July</option>
                                    <option value="08">August</option>
                                    <option value="09">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                    </div>     
                    <div class="col-sm-6">
                            <div class="form-group" id="expiration-date">
                                <label>Expiration Year <span class="text-danger">*</span></label>
                                <select name="card_year" class="form-control" id="card_year">
                                    <option value="19"> 2019</option>
                                    <option value="20"> 2020</option>
                                    <option value="21"> 2021</option>
                                    <option value="22"> 2022</option>
                                    <option value="23"> 2023</option>
                                    <option value="24"> 2024</option>
                                    <option value="25"> 2025</option>
                                    <option value="26"> 2026</option>
                                    <option value="27"> 2027</option>
                                    <option value="28"> 2028</option>
                                    <option value="29"> 2029</option>
                                    <option value="30"> 2030</option>
                                    <option value="31"> 2031</option>
                                    <option value="32"> 2032</option>
                                    <option value="33"> 2033</option>
                                    <option value="34"> 2034</option>
                                    <option value="35"> 2035</option>
                                </select>
                            </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="">Total Amount <span class="text-danger">*</span></label>
                            <input type="text" class="form-control numberonly" name="amount_paid">
                        </div>     
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
    (function($){
        $(document).ready(function(){
            $('#client_payment_form').validate({
                rules:{
                    client_location:"required",
                    client_name:"required",
                    client_email:"required",
                    card_owner:"required",
                    card_no:"required",
                    card_cvv:"required",
                    card_month:"required",
                    card_year:"required",
                    amount_paid:"required"
                },
                submitHandler:function(form){
                    // send ajax to server to process the card and save the record in database
                    $.ajax({
                        type:"post",
                        url:"<?= admin_url( 'admin-ajax.php' ); ?>",
                        data:$(form).serialize(),
                        dataType:"json",
                        beforeSend:function(){
                            jQuery('#confirm-purchase').html(`<p>Processing... <span> <img src="<?= get_template_directory_uri(); ?>/assets/img/processing.gif" /></span></p>`);
                        },
                        success:function(data){
                            if(data.status==="success"){
                                console.log('payment successfull');
                                
                                // let id=data.data.id;
                                console.log('redirecting...');
                                jQuery('#confirm-purchase').html('Redirecting...');
                                window.location.replace(`https://www.gamexterminatingservices.com/payment_receipts?payment_id=${data.payment_id}`);
                            }
                            else if(data.status==="failed"){
                                //display error on screen
                                console.log(data.message);
                                console.log(data.code);
                                switch (data.code) {
                                    case 'invalid_expiry_month':
                                        jQuery('.errors').html('<p>Card month is invalid</p>');
                                        break;
                                    case 'invalid_expiry_year':
                                        jQuery('.errors').html('<p>Card year is invalid</p>');
                                        break;
                                    case 'parameter_invalid_integer':
                                        jQuery('#amount').parent.addClass('has-error').focus();
                                        jQuery('.errors').html('<p>Invalid Amount</p>');
                                        break;
                                    case 'card_declined':
                                        jQuery('.errors').html('<p>Card Declined: Please use a valid card details</p>');
                                        break;
                                    default:
                                        jQuery('.errors').html('<p>Something Went Wrong, Please try again later</p>');
                                        break;
                                }

                                jQuery('#confirm-purchase').html('Pay');
                            }   
                        }
                    });
                }
            })
        });
    })(jQuery);
</script>

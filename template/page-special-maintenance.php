<?php

 /* Template Name: Special Maintenance Contract */ 

get_header();

global $wpdb;

$filled_by_client=false;

if(!empty($_GET['contract-id'])){
    $contract_id=(new GamFunctions)->encrypt_data($_GET['contract-id'],'d');
    $client_data=$wpdb->get_row("select * from {$wpdb->prefix}special_contract where id='$contract_id'",'ARRAY_A');
 
    if($client_data && $client_data['form_status']=="form_filled_by_staff"){
       $filled_by_client=true;
    }
    else{
        wp_redirect((new Maintenance)->thankyouPageUrl());
    }    
}
 

?>

<section id="content">
   <section class="formSection">
      <div class="container">
         <form method="post" id="speicalcontract" class="maintenance-forms" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" name="speicalcontract">

            <h2 class="form-head">Special Maintenance Contract</h2>

            <?php (new GamFunctions)->getFlashMessage(); ?>

			<?php wp_nonce_field('special_contract'); ?>

            <input type="hidden" name="db_id">
            <input type="hidden" name="db_code">            

            <!-- COMMON FIELDS IN ALL CONTRACT PAGES  -->
            <?php get_template_part('/template/maintenance-forms/common-fields'); ?>

            <?php if($filled_by_client): ?>
                <input type="hidden" name="contract_id" value="<?= $client_data['id']; ?>">
                <input type="hidden" name="method" value="update">
                <input type="hidden" name="action" value="special_contract_client_cc_part">
            <?php else: ?>
                <input type="hidden" name="callrail_id" value="unknown">
                <input type="hidden" name="method" value="insert">
                <input type="hidden" name="action" value="special_contract">
            <?php endif; ?>
                    

            <!-- SHOW FILLED FILEDS OR INPUT FILEDS IF NEW FORM  -->
            <?php if(!$filled_by_client): ?>
                <?php get_template_part("/template/maintenance-forms/special",null,['data'=>'form_fields']); ?>
            <?php else: ?>
                <?php get_template_part("/template/client-area/special",null,['data'=>$client_data]); ?>
            <?php endif; ?>

            <?php get_template_part("/template/maintenance-forms/credit-card-signature"); ?>

            <!-- pest input field  -->
            <?php if(!$filled_by_client): ?>
               <?php get_template_part('/template/maintenance-forms/pests'); ?>
            <?php else: ?>
               <?= (new Maintenance)->includedExludedPests($client_data['pests_included']); ?>
            <?php endif; ?>

            <!-- disclaimer text -->
            <?= (new Maintenance)->mail_template(); ?>
            
            <?php get_template_part("/template/maintenance-forms/special",null,['data'=>'checkbox_line']); ?>

            <!-- SUBMIT BUTTON  -->
            <div class="row">
               <div class="col-sm-12 text-center">
                  <div class="errors"></div>
                  <div class="form-group">
                     <button class="sendform btn btn-danger btn-lg">Submit</button>
                  </div>
               </div>
            </div>
      </div>
      </form>
      </div>   
   </section>
</section>

<?php get_template_part('template-parts/contract-otp-verification'); ?>

<script>

const client_address = document.getElementById('client_address');

let autocomplete_client_address;
let filled_by_office=false;

(function($){
    $(document).ready(function(){
        
        if($('#client_address').length){
            // intialise map from google-autocomplete.js
            initMap('client_address', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    client_address.value = place.formatted_address;
                    autocomplete_client_address = client_address.value;
                });
            });
        }

        $('.filled-by-staff').on('click',function(){
            if(this.checked){
                filled_by_office=true;
                $('.cc_field').prop('disabled',true);
                $('.cc_details_box').addClass('hidden');
                $('#speicalcontract input[name="action"]').val('special_maintenance_by_staff');
            }else{
                filled_by_office=false;
                $('.cc_field').prop('disabled',false);
                $('.cc_details_box').removeClass('hidden');
                $('#speicalcontract input[name="action"]').val('special_contract');
            }
        });

        $("#speicalcontract").validate({
            rules:{
                service_type:"required",
                client_name:"required",
                client_email:{
                    email:true,
                    required:true,
                    remote:{
                        url : my_ajax_object.ajax_url,
                        data:{
                            action : "check_for_banned_email",
                            email : function(){
                            return $('#speicalcontract input[name="client_email"]').val()
                            }
                        },
                        type: "post"
                    }
                },
                client_phone:{
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                    alphanumeric: true
                },
                cost:{
                    required:true,
                    number:true
                },
                days:{
                    required:true,
                    number:true
                },
                from_date:"required",
                to_date:"required",
                client_location:"required",
                branch_id:"required",
                client_address:"required",
                checkterms:"required",
                paid_returns	:"required",
                "card_details[creditcardnumber]":{
                    required: true,
                    maxlength:16
                },
                "card_details[cc_month]":"required",
                "card_details[cc_year]":"required",
                "card_details[cccode]":{
                    required:true,
                    maxlength:4
                }
            },
            messages:{
               client_email :{
                  remote : ERROR_MESSAGES.invalid_email
               }
            },
            submitHandler: function(form) {

                if($('#client_address').length){
                    // first check if address is same as selected from places dropdown
                    if(autocomplete_client_address !== client_address.value){
                        alert('Please make sure address is selected from suggessted address');
                        return false;
                    }
                }

                let maintenance_price = $('#speicalcontract input[name="cost"]').val();
                maintenance_price = parseFloat(maintenance_price);
                if(maintenance_price <= 59 && !isOtpVerified){
                    $('#codeVerification').modal('show');
                    return false;
                }                

                $('.sendform').attr('disabled',true);

                if(!filled_by_office){
                    if(signaturePad.isEmpty()){
                        alert('please fill the signature pad first');
                        $('.sendform').attr('disabled',false);
                        return false;
                    }
                    else{
                        let data = signaturePad.toDataURL('image/png');
                        let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");                        

                        $('#speicalcontract input[name="signimgurl"]').val(img_data);
                        return true;
                    }
                }
                else{
                    return true;
                }
            } 
        });

        $('#speicalcontract input[name=service_type]').on('change',function(){
            if($(this).val()=="monthly" || $(this).val()=="bi_monthly" ){

                var service_type_html=`
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="cost">Cost Per Visit</label>
                                        <input name="cost" type="text" class="form-control" placeholder="e.g. $100">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="cost">Every</label>
                                        <input name="days" type="text" class="form-control" placeholder="e.g. 7 Days">
                                    </div>
                                </div>
                            </div>
                `;
            }
            else if($(this).val()=="on_demand"){

                var service_type_html=`
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="cost">Cost Per visit</label>
                            <input type="text" name="cost" class="form-control" placeholder="e.g. $100">
                        </div>
                    </div>
                </div>
                `;

            }

            $('.service-box').html(service_type_html);

        });

    });
})(jQuery);

</script> 
      

<?php
get_footer();
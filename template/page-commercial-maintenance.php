<?php /* Template Name: Commercial Maintenance Contract */ 
get_header();

global $wpdb;
$filled_by_client=false;

if(!empty($_GET['contract-id'])){
    $contract_id=(new GamFunctions)->encrypt_data($_GET['contract-id'],'d');
    $client_data=$wpdb->get_row("select * from {$wpdb->prefix}commercial_maintenance where id='$contract_id'",'ARRAY_A');

    if($client_data && $client_data['form_status']=="form_filled_by_staff"){
       $filled_by_client=true;
    }
    else{
        return wp_redirect((new Maintenance)->thankyouPageUrl());
    }
 
}
?>

<section id="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <form method="post" id="commercialcontract" action="<?= admin_url('admin-post.php'); ?>" class="res-form maintenance-forms" >

                    <h2 class="form-head">Commercial Maintenance Contract</h2>

                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <?php wp_nonce_field('commercial_contract'); ?>

                    <input type="hidden" name="db_id">
                    <input type="hidden" name="db_code">

                    <!-- COMMON FIELDS IN ALL CONTRACT PAGES  -->
                    <?php get_template_part('/template/maintenance-forms/common-fields'); ?>        

                    <?php if($filled_by_client): ?>
                        <input type="hidden" name="contract_id" value="<?= $client_data['id']; ?>">
                        <input type="hidden" name="method" value="update">
                        <input type="hidden" name="action" value="commercial_contract_client_cc_part">
                    <?php else: ?>
                        <input type="hidden" name="callrail_id" value="unknown">
                        <input type="hidden" name="method" value="insert">
                        <input type="hidden" name="action" value="commercial_contract">
                    <?php endif; ?>


                    <!-- DISPLAY FILLED FILEDS OR INPUT FIELDS BASED ON CLIENT FILLED OR STAFF  -->
                    <?php if(!$filled_by_client): ?>
                        <?php get_template_part('/template/maintenance-forms/commercial',null,['data'=>'form_fields']); ?>
                    <?php else: ?>
                        <?php get_template_part("/template/client-area/commercial",null,['data'=>$client_data]); ?>
                    <?php endif; ?>
                    
                    <!-- CREDIT CARD & SIGNATURE TEMPLATE  -->
                    <?php get_template_part('/template/maintenance-forms/credit-card-signature'); ?>

                    <!-- pest input field  -->
                    <?php if(!$filled_by_client): ?>
                        <?php get_template_part('/template/maintenance-forms/pests'); ?>
                    <?php else: ?>
                        <?= (new Maintenance)->includedExludedPests($client_data['pests_included']); ?>
                    <?php endif; ?>

                    <!-- disclaimer text -->
                    <?= (new Maintenance)->mail_template(); ?>

                    <?php get_template_part('/template/maintenance-forms/commercial',null,['data'=>'checkbox_line']); ?>
                    
                    <!-- SUBMIT BUTTON  -->
                    <div class="text-center">
                        <button class="btn btn-primary btn-lg sendform"><span><i class="fa fa-paper-plane"></i></span> Submit</button>    
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('template-parts/contract-otp-verification'); ?>

<script>

const client_address = document.getElementById('client_address');

let autocomplete_client_address;
let filled_by_office=false;

(function($){
    jQuery(document).ready(function(){

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

        jQuery('.filled-by-staff').on('click',function(){
            if(this.checked){
                filled_by_office=true;
                $('.cc_field').prop('disabled',true);
                $('.cc_details_box').addClass('hidden');
                $('#commercialcontract input[name="action"]').val('commercial_maintenance_form_by_staff');
            }
            else{
                filled_by_office=false;
                $('.cc_field').prop('disabled',false);
                $('.cc_details_box').removeClass('hidden');
                $('#commercialcontract input[name="action"]').val('commercial_contract');
            }
        });
            
        jQuery("#commercialcontract").validate({
            rules:{
                establishement_name:"required",
                person_in_charge:"required",
                branch_id:"required",
                client_address:"required",
                establishment_phoneno:{
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                    alphanumeric: true
                },
                res_person_in_charge_phone_no:{
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                    alphanumeric: true
                },
                client_email:{
                    email:true,
                    required:true,
                    remote:{
                        url : my_ajax_object.ajax_url,
                        data:{
                            action : "check_for_banned_email",
                            email : function(){
                            return $('#commercialcontract input[name="client_email"]').val()
                            }
                        },
                        type: "post"
                    }                    
                },
                cost_per_visit:"required",
                notes:"required",
                frequency_of_visit:"required",
                frequency_per	:"required",
                prefered_days	:"required",
                prefered_time	:"required",
                contract_start_date	:"required",
                contract_end_date	:"required",
                checkterms	:"required",
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

                let maintenance_price = $('#commercialcontract input[name="cost_per_visit"]').val();
                maintenance_price = parseFloat(maintenance_price);
                if(maintenance_price <= 59 && !isOtpVerified){
                    $('#codeVerification').modal('show');
                    return false;
                }

                $('.sendform').attr('disabled',true);

                if(!filled_by_office){
                    if(signaturePad.isEmpty()){
                        $('.sendform').attr('disabled',false);
                        alert('please fill the signature pad first');
                        return false;
                    }
                    else{
                        let data = signaturePad.toDataURL('image/png');
                        let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");                        

                        jQuery('#commercialcontract input[name="signimgurl"]').val(img_data);
                        return true;
                    }                            
                }
                else{
                    return true;
                }
            }
        });

    });

})(jQuery);

</script> 
<?php 
get_footer();	  
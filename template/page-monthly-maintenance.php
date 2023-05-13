<?php /* Template Name: Monthly Maintenance */

get_header();

global $wpdb;

$filled_by_client=false;

if(!empty($_GET['contract-id'])){

   $contract_id = (new GamFunctions)->encrypt_data($_GET['contract-id'],'d');
   $contract = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id);

   if($contract && $contract->form_status=="form_filled_by_staff"){
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
         <form id="monthlycontract" class="maintenance-forms">

            <?php wp_nonce_field('maintenance_contracts'); ?>

            <input type="hidden" name="signimgurl">
            <input type="hidden" name="type" value="monthly">

            <input type="hidden" name="db_id">
            <input type="hidden" name="db_code">

            <?php if(isset($_GET['redirect_url']) && !empty($_GET['redirect_url'])): ?>
               <input type="hidden" name="page_url"   value="<?= $_GET['redirect_url']; ?>">
            <?php else: ?>
               <input type="hidden" name="page_url"   value="<?= site_url().$_SERVER['REQUEST_URI']; ?>">
            <?php endif; ?>

            <?php if(!empty($_GET['invoice_id'])): ?>
               <input type="hidden" name="invoice_id" value="<?= $_GET['invoice_id']; ?>">
            <?php endif; ?>

            <?php if(isset($_GET['show_receipt'])): ?>
               <input type="hidden" name="show_receipt" value="true">
            <?php endif; ?>
            
            <!-- if is part of invoice flow then add the hidden field  -->
            <?php if(!empty($_GET['invoice-flow']) && @$_SESSION['invoice_step']=="maintenance_plan"): ?>
               <span class="label label-primary">Invoice Step</span>
               <input type="hidden" name="invoice_step" value="maintenance_plan">
            <?php endif; ?>
			
            <?php if($filled_by_client): ?>
               <input type="hidden" name="method" value="update">      
               <input type="hidden" name="contract_id" value="<?= $contract->id; ?>">
               <input type="hidden" name="action" value="monthly_maintenance_credit_card_part">
            <?php else: ?>
               <input type="hidden" name="method" value="insert">      
               <input type="hidden" name="callrail_id" value="unknown">
               <input type="hidden" name="action" value="monthly_maintenance">
            <?php endif; ?>


            <?php if((new Technician_details)->is_technician_logged_in()): ?>
               <span class="label label-primary"><i class="fa fa-user"></i> <?= (new Technician_details)->get_technician_name(); ?></span>
               <input type="hidden" name="technician_id" value="<?= (new Technician_details)->get_technician_id(); ?>">
            <?php endif; ?>

            <h2 class="form-head">Monthly Maintenance Contract</h2>

            <?php (new GamFunctions)->getFlashMessage(); ?>

            <?php if(!$filled_by_client): ?>
                  <?php get_template_part('/template/maintenance-forms/monthly',null,['data'=>'form_fields']); ?>
            <?php else: ?>
               <?php get_template_part('template/client-area/maintenance-client-part',null,['data'=>$contract]); ?>
            <?php endif; ?>
            

            <!-- credit card input fields  -->
            <?php get_template_part('/template/maintenance-forms/credit-card-signature'); ?>
            
            <!-- pest input field  -->
            <?php if(!$filled_by_client): ?>
               <?php get_template_part('/template/maintenance-forms/pests'); ?>
            <?php else: ?>
               <?= (new Maintenance)->includedExludedPests($contract->pests_included); ?>
            <?php endif; ?>

            <!-- disclaimer text -->
            <?= (new Maintenance)->mail_template(); ?>
            
            <!-- checkbox line  -->
            <?php get_template_part('/template/maintenance-forms/monthly',null,['data'=>'checkbox_line']); ?>


            <!-- Submit Button  -->
            <div class="row">
               <div class="col-sm-12 text-center">
                  <div class="errors"></div>
                  <div class="form-group">
                     <button class="submit_btn btn btn-danger btn-lg" >Submit</button>
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

   let filled_by_office = false;
   let autocomplete_client_address;

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
               filled_by_office = true;
               $('.cc_field').prop('disabled',true);
               $('.cc_details_box').addClass('hidden');
               $('#monthlycontract input[name="action"]').val('maintenance_staff');
            }else{
               filled_by_office = false;
               $('.cc_field').prop('disabled',false);
               $('.cc_details_box').removeClass('hidden');
               $('#monthlycontract input[name="action"]').val('monthly_maintenance');
            }
         });

         $("#monthlycontract").validate({
            rules:{
               client_name:"required",
                     client_location:"required",
                     branch_id:"required",
                     "cost_annual":{
                              required:true,
                              number:true
                     },
                     client_phone_no:{
                        required: true,
                        minlength: 10,
                        maxlength: 12,
                        alphanumeric: true
                     },
                     client_address:"required",
                     client_email:{
                        email:true,
                        required:true,
                        remote:{
                           url : my_ajax_object.ajax_url,
                           data:{
                              action : "check_for_banned_email",
                              email : function(){
                                 return $('#monthlycontract input[name="client_email"]').val()
                              }
                           },
                           type: "post"
                        }                        
                     },
                     cost_per_month:"required",
                     notes:"required",
                     contract_start_date:"required",
                     contract_end_date:"required",
                     checkterms:"required",
                     paid_returns:"required",
                     "card_details[creditcardnumber]":{
                        required: true,
                        maxlength:16
                     },
                     "card_details[cc_month]":"required",
                     "card_details[cc_year]":"required",
                     "card_details[cccode]":{
                        required: true,
                        maxlength: 4
                     }
            },
            messages:{
               client_email :{
                  remote : ERROR_MESSAGES.invalid_email
               }
            },
            submitHandler: function(form) {

               // first check if address is same as selected from places dropdown
               if($('#client_address').length){
                  if(autocomplete_client_address !== client_address.value){
                     return alert('Please make sure address is selected from suggessted address');
                  }
               }

               let maintenance_price = $('#monthlycontract input[name="cost_per_month"]').val();
               maintenance_price = parseFloat(maintenance_price);

               if(maintenance_price <= 59 && !isOtpVerified){
                  $('#codeVerification').modal('show');
                  return false;
               }

               let isValid = false;

               if(!filled_by_office){
                  if(signaturePad.isEmpty()){
                     alert('please fill the signature pad first');
                  }
                  else{
                     let data = signaturePad.toDataURL('image/png');
                     let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");                        
                     $('#monthlycontract input[name="signimgurl"]').val(img_data);
                     isValid = true;
                  }
               }
               else{
                  isValid = true;
               }

               if(isValid) maintenanceAjaxSubmit(form);

            }
         });

      });
   })(jQuery);

</script>

<?php
get_footer();
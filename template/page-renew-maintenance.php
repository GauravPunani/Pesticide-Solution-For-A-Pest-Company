<?php
/* Template Name: Renew Maintenance*/
get_header();

global $wpdb;
$filled_by_client = false;

if (!empty($_GET['contract-id']) && !empty($_GET['type'])) {
   $contract_id = (new GamFunctions)->encrypt_data($_GET['contract-id'], 'd');
   $type = (new GamFunctions)->encrypt_data($_GET['type'], 'd');

   // if url param is malformed
   if (!empty($contract_id) && !empty($type)) {
      switch ($type) {
         case "monthly":
            $MonthlyQuarterly = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id);
            $data = array('contractType' => $type, 'name' => $MonthlyQuarterly->client_name, 'signature' => $MonthlyQuarterly->signature, 'renew_status' => $MonthlyQuarterly->renew_status);
            break;
         case "quarterly":
            $MonthlyQuarterly = (new MonthlyQuarterlyMaintenance)->getContractById($contract_id);
            $data = array('contractType' => $type, 'name' => $MonthlyQuarterly->client_name, 'signature' => $MonthlyQuarterly->signature, 'renew_status' => $MonthlyQuarterly->renew_status);
            break;
         case "commercial":
            $commercial = (new CommercialMaintenance)->getContractById($contract_id);
            $data = array('contractType' => $type, 'name' => $commercial->establishement_name, 'signature' => $commercial->signature, 'renew_status' => $commercial->renew_status);
            break;
         case "special":
            $special = (new SpecialMaintenance)->getContractById($contract_id);
            $data = array('contractType' => $type, 'name' => $special->client_name, 'signature' => $special->signature, 'renew_status' => $special->renew_status);
            break;
         case "termite":
            $termite = (new YearlyTermite)->getContractById($contract_id);
            $data = array('contractType' => $type, 'name' => $termite->name, 'signature' => $termite->signature, 'renew_status' => $termite->renew_status);
            break;
      }
      if(isset($data['renew_status']) && !empty($data['renew_status'])){
         wp_safe_redirect((new Maintenance)->thankyouPageUrl());
         exit;
      }
   } else {
      wp_safe_redirect(home_url());
      exit;
   }
} else {
   wp_safe_redirect(home_url());
   exit;
}
?>

<?php if (count($data) > 0) : ?>
   <section id="content">
      <section class="formSection">
         <div class="container">
            <form id="renew_maintenance_contract_form" class="maintenance-forms">

               <?php wp_nonce_field('renew_maintenance_contract'); ?>

               <input type="hidden" name="signimgurl" value="">

               <?php if (isset($_GET['redirect_url']) && !empty($_GET['redirect_url'])) : ?>
                  <input type="hidden" name="page_url" value="<?= $_GET['redirect_url']; ?>">
               <?php else : ?>
                  <input type="hidden" name="page_url" value="<?= site_url() . $_SERVER['REQUEST_URI']; ?>">
               <?php endif; ?>

               <input type="hidden" name="action" value="renew_maintenance_contract">
               <input type="hidden" name="contract_id" value="<?= $contract_id; ?>">
               <input type="hidden" name="contract_type" value="<?= $data['contractType']; ?>">
               <input type="hidden" name="contract_name" value="<?= $data['name']; ?>">
               <input type="hidden" name="contract_sign" value="<?= $data['signature']; ?>">

               <?php if ((new Technician_details)->is_technician_logged_in()) : ?>
                  <span class="label label-primary"><i class="fa fa-user"></i> <?= (new Technician_details)->get_technician_name(); ?></span>
                  <input type="hidden" name="technician_id" value="<?= (new Technician_details)->get_technician_id(); ?>">
               <?php endif; ?>

               <h2 class="form-head">Renew <?= ucwords($data['contractType']); ?> Maintenance Contract</h2>

               <?php (new GamFunctions)->getFlashMessage(); ?>

               <!-- Pass contract data in different template as per contract type -->
               <?php
               switch ($data['contractType']) {
                  case "monthly":
                     get_template_part('template/client-area/maintenance-client-part', null, ['data' => $MonthlyQuarterly]);
                     break;
                  case "quarterly":
                     get_template_part('template/client-area/quarterly-maintenance', null, ['data' => $MonthlyQuarterly]);
                     break;
                  case "commercial":
                     get_template_part('template/client-area/commercial', null, ['data' => (array) $commercial]);
                     break;
                  case "special":
                     get_template_part("/template/client-area/special", null, ['data' => (array) $special]);
                     break;
                  case "termite":
                     get_template_part("/template/client-area/yearly-termite", null, ['data' => $termite]);
                     break;
               }
               ?>

               <!-- CREDIT CARD AND SIGNATURE  FIELDS  -->
               <?php get_template_part('template/maintenance-forms/renew-contract-signature'); ?>

               <!-- disclaimer text -->
               <?= (new Maintenance)->mail_template(); ?>

               <!-- CHECKBOX WITH AGREEMENT LINE  -->
               <?php get_template_part('template/maintenance-forms/quarterly', null, ['data' => 'checkbox_line']); ?>

               <!-- submit button  -->
               <div class="row">
                  <div class="col-sm-12 text-center">
                     <div class="form-group">
                        <button class="btn btn-danger btn-lg sendform">Submit</button>
                     </div>
                  </div>
               </div>
         </div>
         </form>
         </div>
      </section>
   </section>
<?php endif; ?>

<script>
   const client_address = document.getElementById('client_address');

   let autocomplete_client_address;
   let filled_by_office = false;

   (function($) {
      $(document).ready(function() {

         $("#renew_maintenance_contract_form").validate({
            rules: {
               contract_start_date: "required",
               contract_end_date: "required",
               checkterms: "required"
            },
            submitHandler: function(form) {
               let isValid = false;
               if (signaturePad.isEmpty()) {
                  alert('please fill the signature pad first');
               } else {
                  let data = signaturePad.toDataURL('image/png');
                  let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");
                  jQuery('#renew_maintenance_contract_form input[name="signimgurl"]').val(img_data);
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
?>
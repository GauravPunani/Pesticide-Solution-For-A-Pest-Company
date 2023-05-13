<?php

/* Template Name: Technician Signup Form */

get_header();

$freely_parked_vehicles = (new CarCenter)->getFreelyParkedVehicles();
$branches = (new Branches)->getAllBranches();
?>
<div class="container">
   <div class="technician_form">
      <form id="technicianSignupForm" method="POST" class="res-form" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
         <h2 class="form-head text-center">Technician Signup Form</h2>
         <?php (new GamFunctions)->getFlashMessage(); ?>

         <?php wp_nonce_field('technician_signup'); ?>
         <input type="hidden" name="action" value="technician_signup">
         <input type="hidden" name="signature_data" value="">
         <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

         <div class="row">

            <div class="col-md-6">
               <div class="form-group">
                  <label for="first-name">First name</label>
                  <input type="text" class="form-control" id="first_name" placeholder="Enter First Name" name="first_name">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="last-name">Last Name</label>
                  <input type="text" class="form-control" id="Last_name" placeholder="Enter Last Name" name="last_name">
               </div>
            </div>

            <!-- Email  -->
            <div class="col-md-6">
               <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="dob">Date Of Birth:</label>
                  <input type="date" class="form-control" name="date_of_birth">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="">Select your state</label>
                  <select name="branch_id" class="form-control select2-field">
                     <option value="">Select</option>
                     <?php if (is_array($branches) && count($branches) > 0) : ?>
                        <?php foreach ($branches as $branch) : ?>
                           <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                        <?php endforeach; ?>
                     <?php endif; ?>
                  </select>
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="Home-Address">Home Address:</label>
                  <input type="text" class="form-control" placeholder="Enter Home Address" id="home_address" name="home_address">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="social-security">Social Security:</label>
                  <input type="text" class="form-control" id="social_security" placeholder="Enter Social Security" name="social_security">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="drivers-license">Upload Drivers License:</label>
                  <input accept="image/*" type="file" class="form-control" id="drivers_license" name="drivers_license">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" id="password" name="password" autocomplete="on">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label for="password">Confirm Password</label>
                  <input type="password" class="form-control" name="password_confirm" autocomplete="on">
               </div>
            </div>

            <div class="col-sm-12">
               <div class="form-group">
                  <label for="">Do you've a pesticde license ?</label>
                  <label class="radio-inline"><input type="radio" name="have_pesticie_license" value="yes">Yes</label>
                  <label class="radio-inline"><input type="radio" name="have_pesticie_license" value="no">No</label>
               </div>
            </div>
            <!-- PESTICIDE LICENESE DETAILS -->
            <div class="pesticide_license_details hidden">
               <div class="col-md-6">
                  <div class="form-group">
                     <label for="pesticide-license">Pesticide License No.</label>
                     <input type="text" class="form-control" name="pesticide_license_no">
                  </div>
               </div>

               <div class="col-md-6">
                  <div class="form-group">
                     <label for="pesticide-license">Pesticide License Proof</label>
                     <input accept="image/*" type="file" class="form-control" name="pesticide_license">
                  </div>
               </div>
            </div>

            <div class="col-md-12 ">
               <div class="alert alert-info">
                  <strong>Please fillup form carefully this information is also used in w9 form</strong>
               </div>
            </div>

            <!-- signature  -->
            <div class="form-group signature-area">
               <div class="row">
                  <div class="col-40 col-md-offset-2 c-name">
                     <div id="signArea">
                        <label for="sign">Technician Signature</label>
                        <div class="sig sigWrapper" style="height:auto;">
                           <div class="typed"></div>
                           <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas>
                           <button type="button" onclick="clearCanvas()" class="btn btn-danger">Clear Signature</button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>


            <div class="col-md-12 text-center">
               <button id="submit_btn" class="btn btn-primary btn-lg"><span><i class="fa fa-paper-plane"></i></span> Submit</button>

            </div>

         </div>

      </form>
   </div>
</div>
<script type="text/javascript">
   let parking_address = document.getElementById('parking_address');
   let home_address = document.getElementById('home_address');

   let signaturePad;
   let vehicle_parking_address;
   let technician_home_address;


   (function($) {

      $(document).ready(function() {

         initMap('home_address', (err, autoComplete) => {
            autoComplete.addListener('place_changed', function() {
               let place = autoComplete.getPlace();
               home_address.value = place.formatted_address;
               technician_home_address = home_address.value;
            });
         });

         if ($('#sign-pad').length) {
            signaturePad = new SignaturePad(document.getElementById('sign-pad'), {
               backgroundColor: 'rgb(255, 255, 255)',
               penColor: 'rgb(0, 0, 0)'
            });
         }

         $.validator.addMethod("noSpace", function(value, element) {
            return value.indexOf(" ") < 0 && value != "";
         }, "Space not allowed");

         $('#technicianSignupForm').validate({
            rules: {
               first_name: "required",
               last_name: "required",
               email: {
                  required: true,
                  email: true,
                  remote: {
                     url: "<?= admin_url('admin-ajax.php'); ?>",
                     data: {
                        action: "is_email_exist",
                        "_wpnonce": "<?= wp_create_nonce('is_email_exist') ?>",
                     },
                     type: "post"
                  }
               },
               date_of_birth: "required",
               home_address: "required",
               branch_id: "required",
               social_security: "required",
               drivers_license: "required",
               password: {
                  minlength: 6
               },
               password_confirm: {
                  minlength: 6,
                  equalTo: "#password"
               },
               pesticide_license_no: "required",
               pesticide_license: "required",
            },
            messages: {
               email: {
                  remote: "Email is already registered"
               },
               username: {
                  remote: "Username already taken"
               },
               password_confirm: {
                  equalTo: "password & verify password field do not match"
               },
            },
            submitHandler: function(form) {
               if (signaturePad.isEmpty()) {
                  alert('please fill the signature pad first');
                  return false;
               } else {

                  if (technician_home_address !== home_address.value) {
                     alert('Please make sure home address is from the suggest addresses');
                     home_address.focus();
                     return false;
                  }

                  //disable the submit button
                  $('#technicianSignupForm #submit_btn').prop('disabled', true).val('processing...');

                  let data = signaturePad.toDataURL('image/png');
                  let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");

                  $('#technicianSignupForm input[name="signature_data"]').val(img_data);
                  return true;
               }
            }
         });

         $('input[name="have_pesticie_license"]').on('change', function() {
            const have_pesticie_license = $(this).val();

            if (have_pesticie_license === "yes") {
               $('.pesticide_license_details').removeClass('hidden');
            } else {
               $('.pesticide_license_details').addClass('hidden');
            }

         });
      });

   })(jQuery);

   function clearCanvas() {
      signaturePad.clear();
   }
</script>
<?php get_footer(); ?>
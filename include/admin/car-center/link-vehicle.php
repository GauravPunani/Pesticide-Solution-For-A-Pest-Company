<?php
   global $wpdb;
   $columns = ['id','first_name','last_name'];
   $technicians = (new Technician_details)->getWithoutVehicleTechnicians($columns);
   $availableVehicles = (new CarCenter)->getFreelyParkedVehicles();
?>

<div class="container">
   <div class="row">
      <div class="col-md-offset-2 col-md-6">
         <div class="card full_width table-responsive">
            <div class="card-body">

               <h3 class="page-header">Link Vehicle</h3>
               <?php (new GamFunctions)->getFlashMessage(); ?>               

               <form id="linkVehicleForm"   method="POST" class="res-form" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                  
				      <?php wp_nonce_field('link_vehicle'); ?>
                  <input type="hidden" name="action" value="link_vehicle">
                  <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                  <div class="form-group">
                     <label for="owner-name">Select Technician</label>
                     <select class="form-control select2-field" name="technician_id">
                        <?php foreach($technicians as $technician): ?>
                           <option value="<?= $technician->id; ?>"><?= $technician->first_name." ".$technician->last_name; ?></option>
                        <?php endforeach; ?>   
                     </select>
                  </div>

                  <div class="form-group">
                     <label for="">Select Vehicle</label>
                     <select name="vehicle_id" class="form-control select2-field">
                        <option value="">Select</option>
                        <?php if(is_array($availableVehicles) && count($availableVehicles) > 0): ?>
                           <?php foreach($availableVehicles as $vehicle): ?>
                              <option value="<?= $vehicle->id; ?>"><?= $vehicle->year." ".$vehicle->make." ".$vehicle->model. " ($vehicle->plate_number)"; ?></option>
                           <?php endforeach; ?>

                        <?php endif; ?>
                     </select>
                  </div>

                  <div class="form-group">
                     <label for="">Parking Address</label>
                     <input type="text" class="form-control" name="parking_address" id="parking_address">
                  </div>
                  
                  <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Vehicle</button>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
   (function($){
      $(document).ready(function(){

         // intialise map from google-autocomplete.js
         initMap('parking_address', (err, autoComplete) => {
               autoComplete.addListener('place_changed', function() {
                  let place = autoComplete.getPlace();
                  parking_address.value = place.formatted_address;
                  autocomplete_parking_address = parking_address.value;
               });
         });

         $('#linkVehicleForm').validate({
            rules: {
               technician_id: 'required',
               vehicle_id: 'required',
               parking_address: 'required',
            }
         });

      });
   })(jQuery);
</script>
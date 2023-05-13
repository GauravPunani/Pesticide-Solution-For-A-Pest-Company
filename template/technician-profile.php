<?php /* Template Name: Technician Information */ ?>
<?php get_header(); 
   ?>
<div class="container" style="padding-top: 20px; ">
   <h2 style="text-align: center;">Technician Profile</h2>
   <form action="/action_page.php">
      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <label for="first-name">First Name</label>
               <input type="text" class="form-control" id="first_name" placeholder="Enter First Name" name="first_name">
            </div>
         </div>
         <div class="col-md-6">
            <div class="form-group">
               <label for="last-name">Last Name</label>
               <input type="text" class="form-control" id="Last_name" placeholder="Enter Last Name" name="Last_name">
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <label for="Address">Address:</label>
               <input type="textarea" class="form-control" id="address" placeholder="Enter Address" name="address">
            </div>
         </div>
         <div class="col-md-6">
            <div class="form-group">
               <label for="drivers-license">Upload Drivers License:</label>
               <input type="file" class="form-control" id="drivers_license" name="drivers_license">
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <div class="form-group">
                  <label for="dob">Date Of Birth:</label>
                  <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="form-group">
               <label for="phone">Phone Number</label>
               <input type="tel" class="form-control" id="phone" placeholder="Enter Phone Number" name="phone">
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <div class="form-group">
                  <label for="plate-number">1st Emergency Contact:</label>
                  <input type="text" class="form-control" id="f_contact" placeholder="Enter 1st Emergency Contact" name="f_contact">
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="form-group">
               <label for="Model-Of-Vehicle">2nd Emergency Contact:</label>
               <input type="text" class="form-control" id="s_contact" placeholder="Enter 2nd Emergency Contact" name="s_contact">
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-12">
            <div class="form-group">
               <label for="pesticide-license">Pesticide License Or Certificate Uploaded:</label>
             <input type="file" class="form-control" id="pesticide_license" name="pesticide_license">
             </div>
         </div>
      </div>
      <button style="float: right;" type="submit" class="btn btn-primary">Submit</button>
   </form>
</div>
<?php get_footer();?>
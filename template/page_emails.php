<?php /* Template Name: Emails */

get_header();

   global $wpdb;
?>
<section id="content">
   
   <!-- Form -->
      <div class="container">
         <form method="post" id="verifyemails" class="maintenance-forms" 	name="res-form">   
	            <!-- <input type="hidden" name="type" value="monthly"> -->
	            <input type="hidden" name="action" value="verify_email_cli_emails">


			    <!----client address---->
			    <div class="form-group">
			        <label for="address">Client Name</label>
			      <input type="text" class="form-control" name="client_name" placeholder="Client Name">
			    </div>


			    <!------Client phone number----->
			    <div class="form-group">
			        <label for="address">Address</label>
			        <textarea class="form-control" name="address"  rows="5" placeholder="Address"></textarea>
			    </div>

			       <!-----Client email --->
			    <div class="form-group">
			        <label for="Email">Email</label>
			        <input type="email" class="form-control" name="email" placeholder="Email">
			    </div>


			    <!------Client phone number----->
			    <div class="form-group">
			        <label for="number">Client phone number</label>
			        <input type="text" class="form-control client_phone_no phone_no" maxlength="12" name="phone" placeholder="Phone Number">
			    </div>

			 
			    <!---------Cost per month----->
			    <div class="form-group">
			        <label for="Cost">Reocurring Status</label>
			        <input type="text" class="form-control" name="reocurring_status" placeholder="Reocurring Status">
			    </div>


				<div class="row">
					<div class="col-sm-12 text-center">
					<div class="errors"></div>
						<div class="form-group">
						 <button class="sendform btn btn-danger btn-lg">Submit</button>
						</div>
					</div>
				</div>
      </form>

      </div>   
</section>

<script>

(function($){
   $(document).ready(function(){


      $("#verifyemails").validate({
         rules:{
            	email:{
                     email:true,
                     required:true
                },
         },

         messages: {
			    email: 'Enter a valid email',
			  
			  },
	  submitHandler: function(form) {
	    form.submit();
	  }

      });
      
      

   });
})(jQuery);


 jQuery(document).ready(function(){
         jQuery('#verifyemails').on('submit',function(e){
             e.preventDefault();
   
             jQuery.ajax({
                 type:"post",
                 url:"<?php echo admin_url('admin-ajax.php'); ?>",
                 data:$('#verifyemails').serialize(),
                 dataType:"json",
                 beforeSend:function(){
   
                 },
                 success:function(data){  
                  alert("Technician Added");
                 }
             })
   
         });
         
         
     });

</script>
<?php
get_footer();
<?php /* Template Name: Technician information */ ?>
<?php get_header();
   // get the user information 
   global $wpdb;

             if(isset($_SESSION['technician_signup'])){
                wp_redirect('/technician-signup-form');
            }
   
    ?>
<div class="container" style="padding-top: 20px; ">
   <div style="text-align: center;" class="confirmation-box">
      <form  id="technician_confirmation_form" action="" method="POST">
         <input type="hidden" name="action" value="insert_technician_signup_code">
         <input type="hidden" name="type" value="Technician-Signup">
         <input type="text" name="name" placeholder="Please Enter your Name">
         <p style="text-align: center;">You need permission from office by requesting a code to Signup</p>
         <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> <span id="confirm_submit_btn">Request Code</span></button>                
      </form>
   </div>
   <div id="verification-box" class="verification-box" style="display: none;">
      <form action="" id="code_verification_form">
         <input type="hidden" name="action" value="verify_technician_signup_code">
         <input type="hidden" id="s_db_id" name="db_id" >
         <div class="form-group">
            <label for="">Please enter the verification code</label>
            <input type="text" name="code" maxlength="6" class="form-control">
         </div>
         <button id="verification_submit_btn" class="btn btn-primary">Verify &amp; Submit</button>
      </form>
   </div>
</div>
<script type="text/javascript">
   jQuery(document).ready(function(){
      jQuery('#technician_confirmation_form').on('submit', function(e){
         e.preventDefault();
   
          jQuery.ajax({
   
            type:"POST",
            url:"<?php echo admin_url('admin-ajax.php'); ?>",
            data:jQuery('#technician_confirmation_form').serialize(),
            dataType:"json",
            beforeSend:function(){
   
            },
            success:function(data){
              $("#s_db_id").val(data.db_id);
              $(".confirmation-box").hide();
              $(".verification-box").show();
   
            }
      })
      })
   
   
   jQuery('#code_verification_form').on('submit', function(e){
         e.preventDefault();
          jQuery.ajax({
   
            type:"POST",
            url:"<?php echo admin_url('admin-ajax.php'); ?>",
            data:jQuery('#code_verification_form').serialize(),
            dataType:"json",
            beforeSend:function(){
   
            },
            success:function(data){
            }
      })
      })
   
   });
</script>
<?php get_footer();?>
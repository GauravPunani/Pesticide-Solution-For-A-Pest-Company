<?php

/* Template Name: Employee Login*/

get_header();
$role = '';
if(isset($_GET['role']) && !empty($_GET['role'])) $role = $_GET['role'];

?>
<style>
	.login-form {
		width: 340px;
    	margin: 50px auto;
	}
    .login-form form {
    	margin-bottom: 15px;
        background: #f7f7f7;
        box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
        padding: 30px;
    }
    .login-form h2 {
        margin: 0 0 15px;
    }
    .form-control, .btn {
        min-height: 38px;
        border-radius: 2px;
    }
    .btn {        
        font-size: 15px;
        font-weight: bold;
    }
</style>
<div class="container">
    <div class="col-md-12">
        <div class="login-form">
            <form id="employeeLoginForm" action="" method="post">
                <h2 class="text-center">Employee Log in</h2>       
                <div class="login-errors"></div>
				
                <?php wp_nonce_field('employee_login'); ?>
                
                <input type="hidden" name="action" value="employee_login">

                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required="required">
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required="required">
                </div>

                <div class="form-group">
                    <label for="role">Select Role</label>
                    <select name="role" id="role" class="form-control">
                        <option value="office_staff" <?= $role == "office_staff" ? 'selected' : ''; ?>>Office Staff</option>
                        <option value="door_to_door_sale" <?= $role == "door_to_door_sale" ? 'selected' : ''; ?>>Door To Door Sale</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" id="submit_btn" class="btn btn-primary btn-block">Log in</button>
                </div>
            </form>
        </div>        
    </div>
</div>
<script>
    (function($){
        $(document).ready(function(){
            $('#employeeLoginForm').validate({
                rules:{
                    username: "required",
                    password: "required",
                    role: "required",
                },
                submitHandler:function(form){
                    // call ajax for user credential checking
                    $.ajax({
                        type:"post",
                        url:"<?= admin_url( 'admin-ajax.php' ); ?>",
                        dataType:"json",
                        data:$(form).serialize(),
                        beforeSend:function(){
                            $('.login-errors').html("");
                            $('#submit_btn').text('Logging...').attr('disbled',true);
                        },
                        success:function(data){
                            if(data.status=="success"){
                                // redirect user to dashbord
                                console.log('username matched');
                                let redirect_to = getUrlParameter('redirect-to');

                                if(redirect_to!=undefined && redirect_to!=''){
                                    window.location.replace(redirect_to);                                
                                }
                                else{
                                    window.location.replace("<?= site_url(); ?>/employee-dashboard");                                
                                }
                            }
                            else{
                                // print incorrect username password error 
                                console.log('incorrect username password');
                                $('.login-errors').html(`<p class='text-danger'>${data.message}</p>`);
                                $('#submit_btn').text('Login').attr('disbled',false);
                            }
                        }
                    })
                }
            })
        });
    })(jQuery);


    var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
}


</script>
<?php
get_footer();
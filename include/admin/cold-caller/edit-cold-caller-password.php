<?php
global $wpdb;

$cold_caller_id = $args['password'];
$cold_caller_data = (new ColdCaller)->getColdCallerById($cold_caller_id);


?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Edit Cold Caller Password</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="edit_cold_caller_password" method="post" action="<?= admin_url('admin-post.php'); ?>">
						<?php wp_nonce_field('update_cold_caller_password'); ?>
                        <input type="hidden" name="action" value="update_cold_caller_password">
                        <input type="hidden" name="cold_caller_id" value="<?= $cold_caller_data->id; ?>">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
						<div class="form-group">
                            <label for="">Password</label>
                            <input type="text" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="">Verify Password</label>
                            <input type="password" class="form-control" name="password_confirm" required>
                        </div>


                        <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Cold Caller Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

var user_data_validation;

    (function($){
        $(document).ready(function(){

            $.validator.addMethod("noSpace", function(value, element) { 
            return value.indexOf(" ") < 0 && value != ""; 
            }, "Space not allowed");

            // validate form 
            user_data_validation=$('#edit_cold_caller_password').validate({
                rules:{

                    password : {
                        minlength : 8
                    },
                    password_confirm : {
                        minlength : 8,
                        equalTo : "#password"
                    },
                },
                messages:{
                    password_confirm:{
                        equalTo:"password & verify password field do not match"
                    },
                }
            });



        })
    })(jQuery);
</script>
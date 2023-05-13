<?php 
/* Template Name: Cold Caller Signup */
get_header();
$branches= (new Branches)->getAllBranches(false);

?>
<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="card full_width table-responsive">
                <div class="card-body jumbotron">
                    <h3 class="text-center page-header">Create Cold Caller Ac</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="create_cold_caller_form" enctype="multipart/form-data" method="post" action="<?= admin_url('admin-post.php'); ?>">

                        <?php wp_nonce_field('create_cold_caller'); ?>
                        
                        <input type="hidden" name="action" value="create_cold_caller">
                        <input type="hidden" name="user_code" value="">
                        <input type="hidden" name="db_id" value="">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <div class="form-group">
                            <label for="">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="">Phone No.</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="">Address (number, street, and apt. or suite no.) </label>
                            <input type="text" class="form-control" name="address" required>
                        </div>

                        <div class="form-group">
                            <label for="">City, state, and ZIP code</label>
                            <input type="text" class="form-control" name="city_state_zipcode" required>
                        </div>

                        <div class="form-group">
                            <label for="">Social security number</label>
                            <input maxlength="9" type="text" class="form-control" name="social_security_number" required>
                        </div>

						<div class="form-group">
                            <label for="">Please attach your driver's license or proof of identification </label>
                            <input accept="image/*" type="file" name="doc_proof" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="">Password</label>
                            <input autocomplete="on" class="form-control" type="password" name="password" id="password">
                        </div>

                        <div class="form-group">
                            <label for="">Confirm Password</label>
                            <input autocomplete="on" class="form-control" name="password_confirm" type="password">
                        </div>
                        
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Cold Caller</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let db_code_id;
    let user_data_validation;
    let code_generated = false;

    (function($){
        $(document).ready(function(){

            // validate form 
            $('#create_cold_caller_form').validate({
                rules:{
                    name:'required',
                    email:{
                        required : true,
                        email:true,
                        remote:{
                            url : "<?= admin_url('admin-ajax.php'); ?>",
                            data:{
                                action : "is_email_exist",
                                "_wpnonce": "<?= wp_create_nonce('is_email_exist'); ?>"
                            },
                            type: "post"
                        }
                    },
                    phone:'required',
                    address:'required',
                    city_state_zipcode:'required',
                    social_security_number:{
                        required: true,
                        maxlength: 9,
                        minlength: 9,
                    },
                    doc_proof: "required",
                    password : {
                        minlength : 6
                    },
                    password_confirm : {
                        minlength : 6,
                        equalTo : "#password"
                    }                    
                },
                messages:{
                    password_confirm:{
                        equalTo:"password & verify password field do not match"
                    },
                    email :{
                        remote : "Email already exist"
                    }
                }
            });

        })
    })(jQuery);

</script>

<?php
get_footer();
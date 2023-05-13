<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form class="res-form" id="doorTodDoorSignupForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                        <h3 class="page-header text-center">Signup Door To Door Sales</h3>

                        <?php wp_nonce_field('door_to_door_sales_signup'); ?>
                        <input type="hidden" name="action" value="door_to_door_sales_signup">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <div class="form-group">
                            <label for="">Name</label>
                            <input type="text" class="form-control" name="name">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" name="address" id="address">
                        </div>

                        <div class="form-group">
                            <label for="address">Phone No.</label>
                            <input type="text" class="form-control" name="phone_no" id="phone_no">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input class="form-control" type="password" name="password" id="password">
                        </div>

                        <div class="form-group">
                            <label for="re_password">Re-enter Password</label>
                            <input class="form-control" type="password" name="re_password" id="re_password">
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-user-plus"></i></span> Sign Up</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#doorTodDoorSignupForm').validate({
                rules:{
                    name: "required",
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
                    address: "required",
                    phone_no: "required",
                    password: {
                        required: true,
                        minlength: 6
                    },
                    re_password : {
                        required: true,
                        minlength : 6,
                        equalTo : "#password"
                    }                
                },
                messages: {
                    email :{
                        remote : "Email already exist"
                    }                    
                }                
            });
        });
    })(jQuery);
</script>
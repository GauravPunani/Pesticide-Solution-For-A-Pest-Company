<?php

$authorisation_url = (new Calendar)->createAuthUrl();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Add New Calendar</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="add_new_calendar_form" action="<?= admin_url('admin-post.php'); ?>" method="post">

                        <?php wp_nonce_field('add_new_calendar'); ?>

                        <input type="hidden" name="action" value="add_new_calendar">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <div class="form-group">
                            <label for="">Calendar Name</label>
                            <input type="text" class="form-control" name="name">
                        </div>

                        <div class="form-group">
                            <label for="">Calendar Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="">Calendar Auth Code</label>
                            <p><a target="_blank" href="<?= $authorisation_url; ?>">Click Here</a> to get redirected to google calendar authorisation page and authorise calendar in order to get the auth code</p>
                            <input type="text" class="form-control" name="auth_code">
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Add New Calendar</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){

            jQuery.validator.addMethod("accept", function(value, element, param) {
                return value.match(new RegExp("." + param + "$"));
            });

            jQuery.validator.addMethod("noSpace", function(value, element) { 
                return value.indexOf(" ") < 0 && value != ""; 
            }, "letters & underscore only");


            $('#add_new_calendar_form').validate({
                rules:{
                    name: "required",
                    email: "required",
                    auth_code: "required",
                }
            })
        })
    })(jQuery);
</script>
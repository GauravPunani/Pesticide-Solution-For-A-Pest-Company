<?php
$branches=(new Branches)->getAllBranches(false);
?>
<div class="container">
    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Create Tracker</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>

                    <form method="post" id="create_tracker_form" action="<?= admin_url('admin-post.php'); ?>">
						<?php wp_nonce_field('create_tracker'); ?>
                        <input type="hidden" name="action" value="create_tracker">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <div class="form-group">
                            <label for="">Tracker Name</label>
                            <input type="text" name="tracker_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Tracking No.</label>
                            <input type="text" name="tracker_no" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Callrail Account</label>
                            <select name="callrail_ac" class="form-control select2-field">
                                <option value="">Select</option>
                                <option value="upstate">gamexterminatingbuffalo@gmail.com</option>
                                <option value="ny_metro">gamexterminating@gmail.com</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Tracker Location</label>
                            <select name="tracker_location" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?= $branch->slug; ?>"><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Tracker</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#create_tracker_form').validate({
                rules:{
                    tracker_no:"required",
                    tracker_location:"required",
                    callrail_ac:"required",
                    tracker_name  : {
                        required : true,
                        remote:{
                            url : "<?= admin_url('admin-ajax.php'); ?>",
                            data:{
                                action : "check_for_tracker_name"                     
                            },
                            type: "post"
                        }
                    },
                    
                },
                messages:{
                    tracker_name :{
                        remote : "Callrail tracker with this name already exist"
                    }
                },
            })
        })
    })(jQuery);
</script>
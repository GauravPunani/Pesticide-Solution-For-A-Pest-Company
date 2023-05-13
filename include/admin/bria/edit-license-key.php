<?php

if(empty($args['edi_license_key_id'])) return;

$license_key_id = sanitize_text_field($args['edi_license_key_id']);

$licnese_key_data = (new Bria)->getLicenseKeyById($license_key_id);

?>
<?php if($licnese_key_data): ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <?php (new GamFunctions)->getFlashMessage(); ?>
                        <h3 class="page-header">Edit License Key</h3>
                        <form id="updateLicenseKeyForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                        
                            <?php wp_nonce_field('update_bria_key'); ?>
                            <input type="hidden" name="action" value="update_bria_key">
                            <input type="hidden" name="license_key_id" value="<?= $license_key_id; ?>">
                            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                            <div class="form-group">
                                <label for="title">Title <small class="text-danger">*</small></label>
                                <input type="text" class="form-control" name="title" value="<?= $licnese_key_data->title; ?>">
                            </div>

                            <div class="form-group">
                                <label for="key">Key <small class="text-danger">*</small></label>
                                <input type="text" class="form-control" id="key" name="key" value="<?= $licnese_key_data->key; ?>">
                            </div>
                            
                            <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Bria Licsense Key</button>

                            <a class="btn btn-success" href="<?= admin_url('admin.php?page=bria-license-keys'); ?>"><span><i class="fa fa-arrow-left"></i></span> Go Back</a>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    (function($){
        $(document).ready(function($){
            $('#updateLicenseKeyForm').validate({
                rules:{
                    title: "required",
                    key: "required",
                }
            })
        })
    })(jQuery);
</script>

<?php else: ?>
    <h1 class="text-danger">No Key Found</h1>
<?php endif; ?>
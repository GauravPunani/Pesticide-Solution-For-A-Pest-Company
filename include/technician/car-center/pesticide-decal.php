<form id="pesticideDecalForm" class="res-form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
    <?php (new GamFunctions)->getFlashMessage(); ?>
    <h3 class="page-header text-center">Pesticide Decal Form</h3>

    <?php wp_nonce_field('upload_pesticide_decal_proof'); ?>
    <input type="hidden" name="action" value="upload_pesticide_decal_proof">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <div class="form-group">
        <label for="decal_proof">Upload Pesticide Decal Proof</label>
        <input class="form-control" type="file" name="decal_proof" id="decal_proof">
    </div>

    <button class="btn btn-primary"><span><i class="fa fa-upload"></i></span> Upload Proof</button>
</form>

<script>
    (function($){
        $(document).ready(function(){
            $('#pesticideDecalForm').validat({
                rules: {
                    decal_proof: "required"
                }
            })
        })
    })(jQuery);
</script>
<?php
    global $wpdb;

    $branch_id = $_GET['branch-id'];
    $branch = (new Branches)->getBranch($branch_id);

?>

<?php if($branch): ?>

<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="card-title text-center">Edit Branch</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="editBranch" method="post" action="<?= admin_url('admin-post.php') ?>">

                        <?php wp_nonce_field('edit_branch_form'); ?>

                        <input type="hidden" name="action" value="edit_branch">
                        <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
                        <input type="hidden" name="branch_id" value="<?= $_GET['branch-id']; ?>">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        
                        <div class="form-group">
                            <label for="">Branch Name</label>
                            <input class="form-control" type="text" name="name" value="<?= $branch->location_name; ?>">
                        </div>
                        <div class="form-group">
                            <label for="">Review Link</label>
                            <input class="form-control" type="text" name="review_link" maxlength="30" value="<?= $branch->review_link; ?>">
                        </div>
						<div class="form-group">
                            <label for="">Status</label>
                            <select name="status" class="form-control select2-field" style="max-width: 100%;">
                                <option value="">Select</option>
								<option value="true" <?php if($branch->status == 1) echo 'selected="selected"'; ?>>Active</option>
								<option value="false" <?php if($branch->status == 0) echo 'selected="selected"'; ?>>Inactive</option>
                                
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Branch</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#editBranch').validate({
                rules: {
                    name: "required",
                    status: "required"
                }
            })
        })
    })(jQuery);
</script>

<?php else: ?>
    <h3 class="text-center text-danger">No Branch Found</h3>
<?php endif; ?>

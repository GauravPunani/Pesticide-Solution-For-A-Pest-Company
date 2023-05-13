<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h4 class="card-title text-center">Add Technician Notice</h4>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                        <?php wp_nonce_field('add_new_technician_dashboard_notice'); ?>
                        <input type="hidden" name="action" value="add_new_technician_dashboard_notice">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <div class="form-group">
                            <label for="">Add Notice</label>
                            <textarea name="notice" id="" cols="30" rows="5" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="">Select Notice Type</label>
                            <select name="type" id="" class="form-control select2-field" required>
                                <option value="">Select</option>
                                <option value="offer">Offer</option>
                                <option value="warning">Warning</option>
                                <option value="information">Information</option>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Add Notice</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
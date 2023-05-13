<?php

$notice=$args['data'];

?>

<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h4 class="card-title text-center">Update Notice</h4>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                        <?php wp_nonce_field('update_technician_dashboard_notice'); ?>
                        <input type="hidden" name="action" value="update_technician_dashboard_notice">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="notice_id" value="<?= $notice->id; ?>">
                        <div class="form-group">
                            <label for="">Edit Notice</label>
                            <textarea name="notice" id="" cols="30" rows="5" class="form-control" required><?= $notice->notice; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="">Edit Notice Type</label>
                            <select name="type" id="" class="form-control select2-field" required>
                                <option value="">Select</option>
                                <option <?= $notice->type=="offer" ? 'selected' : ''; ?> value="offer">Offer</option>
                                <option <?= $notice->type=="warning" ? 'selected' : ''; ?> value="warning">Warning</option>
                                <option <?= $notice->type=="information" ? 'selected' : ''; ?> value="information">Information</option>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
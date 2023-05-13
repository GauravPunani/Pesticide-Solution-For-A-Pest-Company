<div class="container res-form">
    <div class="row">
        <div class="col-sm-12">
            <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                <input type="hidden" name="action" value="office_notes">
                <input type="hidden" name="type" value="invoice">
                <div class="form-group">
                    <label for="">Client Name</label>
                    <input type="text" class="form-control" name="client_name">
                </div>
                <div class="form-group">
                    <label for="">Notes (For office use only)</label>
                    <textarea name="note"  cols="30" rows="5" class="form-control"></textarea>
                </div>
                <button class="btn btn-primary"><span><i class="fa fa-submit"></i></span> Submit</button>
            </form>
        </div>
    </div>
</div>
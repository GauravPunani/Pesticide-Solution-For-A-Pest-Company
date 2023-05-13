<div class="table-responsive">
    <table id="ads_spent_table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tracking Name</th>
                <th>Tracking Phone No.</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(is_array($tracking_nos) && count($tracking_nos)>0): ?>
                <?php foreach($tracking_nos as $key=>$val): ?>
                    <tr>
                        <td><?= $val->id; ?></td>
                        <td><?= $val->tracking_name; ?></td>
                        <td><?= $val->tracking_phone_no; ?></td>
                        <td><a href="<?= $_SERVER['REQUEST_URI']; ?>&tracking_id=<?= $val->id; ?>" class="btn btn-primary"><span><i class="fa fa-eye"></i> View Details</span></a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif ?>                                
        </tbody>
    </table>
</div>

<script>
(function($){
    $(document).ready(function() {
    $('#ads_spent_table').DataTable();
} );
})(jQuery);
</script>
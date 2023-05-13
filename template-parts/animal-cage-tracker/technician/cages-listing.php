<?php
$cages_records = (isset($args['cages_records']) && is_array($args['cages_records']) && count($args['cages_records']) > 0) ? $args['cages_records'] : [];
?>

<?php if(count($cages_records) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Total Quantity</th>
                    <th>Quantity Retrieved</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($cages_records as $cages_record): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($cages_record->date)); ?></td>
                        <td><?= $cages_record->name; ?></td>
                        <td><?= $cages_record->quantity ; ?></td>
                        <td class="partialQuantity__<?= $cages_record->id; ?>"><?= $cages_record->quantity_retrieved ; ?></td>
                        <?php $max_uploadable_quantity = (int) $cages_record->quantity - (int) $cages_record->quantity_retrieved ; ?>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                    <li><a onclick="markCageRecordAsReterived('<?= $cages_record->id; ?>', <?= $max_uploadable_quantity ?>, this)" href="javascript:void(0)"><span><i class="fa fa-check"></i></span> Mark As Retrieved</a></li>

                                    <li><a  onclick="getAnimalCageOfficeNotes('<?= $cages_record->id; ?>')" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View Office Notes</a></li>
                                </ul>
                            </div>                                
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p><b>No Cage Record Found</b></p>
<?php endif; ?>

<!-- Office Notes MODAL  -->
<div id="cageOfficeNotes" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Animal Cage Office Notes</h4>
            </div>
            <div class="modal-body">
                <div class="cage_office_notes"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="cageQuantityUploadModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Animal Cage Upload Quantity</h4>
            </div>
            <div class="modal-body">
                <form id="cageQuantityUploadForm" method="post" action="<?= admin_url('admin-post.php'); ?>" enctype="multipart/form-data">

                    <?php wp_nonce_field('upload_retrieved_quantity'); ?>
                    <input type="hidden" name="action" value="upload_retrieved_quantity">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="record_id">

                    <div class="form-group">
                        <label for="">Quantity retrieved</label>
                        <input class="form-control" type="number" name="quantity_reterieved" required>
                    </div>

                    <button class="btn btn-primary partialSubmitBtn"><span><i class="fa fa-refresh"></i></span> Update Retrieved Quantity</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    (function($){
        $('#cageQuantityUploadForm').validate({
            rules: {
                quantity_reterieved: "required",
            }
        })
    })(jQuery);
    

    function markCageRecordAsReterived(record_id, max_quantity, ref){

        // set the max uplaodable quantity and same as value
        jQuery('#cageQuantityUploadForm input[name="quantity_reterieved"]').attr({
            max: max_quantity,
            min: 1
        }).val(max_quantity);

        // set the record id 
        jQuery('#cageQuantityUploadForm input[name="record_id"]').val(record_id);

        // open the modal
        jQuery('#cageQuantityUploadModal').modal('show');

    }
</script>
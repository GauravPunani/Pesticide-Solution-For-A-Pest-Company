<?php
    $cages_records = (isset($args['data']) && is_array($args['data']) && count($args['data']) > 0) ? $args['data'] : [];
?>


<?php if(count($cages_records) > 0): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Created At</th>
                <th>Reminder/Pickup Date</th>
                <th>Type</th>
                <th>Total Quantity</th>
                <th>Quantity Retrieved</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cages_records as $cages_record): ?>
                <tr>
                    <td><?= $cages_record->client_name; ?></td>
                    <td><?= $cages_record->address; ?></td>
                    <td><?= date('d M Y', strtotime($cages_record->created_at)); ?></td>
                    <td><?= !empty($cages_record->pickup_date) ? date('d M Y', strtotime($cages_record->pickup_date)): ''; ?></td>
                    <td><?= $cages_record->name; ?></td>
                    <td><?= $cages_record->quantity ; ?></td>
                    <td class="partialQuantity__<?= $cages_record->id; ?>"><?= $cages_record->quantity_retrieved ; ?></td>
                    <?php $max_uploadable_quantity = (int) $cages_record->quantity - (int) $cages_record->quantity_retrieved ; ?>

                    <td>

                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                <?php if($cages_record->retrieved == 0 || $cages_record->retrieved==""): ?>
                                    <li><a onclick="markCageRecordAsReterived('<?= $cages_record->id; ?>', <?= $max_uploadable_quantity; ?>)" href="javascript:void(0)"><span><i class="fa fa-edit"></i></span> Update Retrieved Units</a></li>
                                <?php endif; ?>
                                
                                <li><a  onclick="addNotes('<?= $cages_record->id; ?>')" href="javascript:void(0)"><span><i class="fa fa-plus"></i></span> Add Notes</a></li>

                                <li><a  onclick="getAnimalCageOfficeNotes('<?= $cages_record->id; ?>')" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View Office Notes</a></li>

                                <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&edit_notes=true&cage_id=<?= $cages_record->id; ?>"><span><i class="fa fa-edit"></i></span> Edit Notes</a></li>

                                <?php if(strtotime($cages_record->created_at) < strtotime('-31 days') && ($cages_record->retrieved == 0)): ?>
                                    <li><a  onclick="extendCageRecordAlert('<?= $cages_record->id; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-bell"></i></span> Extend Alert</a></li>
                                <?php endif; ?>

                                <li><a onclick="deleteReocrd(<?= $cages_record->id ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Record</a></li>

                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- CAGE QUANTITY RETERIVED MODAL  -->
    <div id="cageQuantityRetrievedModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Animal Cage Upload Quantity</h4>
                </div>
                <div class="modal-body">

                    <form id="cageQuantityRetrievedForm" method="post" action="<?= admin_url('admin-post.php'); ?>" enctype="multipart/form-data">

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



    <!-- ADD NOTES MODAL  -->
    <div id="addCageNoteModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Cage Notes</h4>
                </div>
                <div class="modal-body">

                    <form id="addCageNotesForm">

                        <?php wp_nonce_field('add_cage_notes'); ?>
                        <input type="hidden" name="action" value="add_cage_notes">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="cage_id">

                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="notes" cols="30" rows="5" class="form-control"></textarea>
                        </div>

                        <button id="addNoteBtn" class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Add Note</button>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <!-- EXTEND CAGE ALERT MODAL  -->
    <div id="extendCageAlertModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Extend Cage Alert</h4>
                </div>
                <div class="modal-body">

                    <form id="extendCageAlertForm">

                        <?php wp_nonce_field('extend_cage_alert'); ?>
                        <input type="hidden" name="action" value="extend_cage_alert">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="record_id">

                        <p><b>Note:</b> Date cannot be less than or equal to current alert/pickup date</p>

                        <div class="form-group">
                            <label for="pickup_date">Select date for next reminder/pickup.</label>
                            <input type="date" name="pickup_date" id="pickup_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="office_notes">Notes for office</label>
                            <textarea name="office_notes" id="office_notes" cols="30" rows="5" class="form-control"></textarea>
                        </div>

                        <button id="submit_btn" class="btn btn-primary"><span><i class="fa fa-bell"></i></span> Extend Alert</button>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

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

<?php else: ?>
    <p><b>No Cage Record Found</b></p>
<?php endif; ?>



<script>

    let record_ref;

    function addNotes(cage_id){
        jQuery('#addCageNotesForm input[name="cage_id"]').val(cage_id)
        jQuery('#addCageNoteModal').modal('show');
    }

    function markCageRecordAsReterived(record_id, max_quantity){

        // set the max uplaodable quantity and same as value
        jQuery('#cageQuantityRetrievedForm input[name="quantity_reterieved"]').attr({
            max: max_quantity,
            min: 1
        }).val(max_quantity);

        // set the record id 
        jQuery('#cageQuantityRetrievedForm input[name="record_id"]').val(record_id);

        // open the modal
        jQuery('#cageQuantityRetrievedModal').modal('show');

    }

    function extendCageRecordAlert(record_id, ref){

        record_ref = ref;

        jQuery('#extendCageAlertForm input[name="record_id"]').val(record_id);
        jQuery('#extendCageAlertModal').modal('show');
    }

    function deleteReocrd(cage_id, ref){
        if(!confirm('Are you sure you want to delete this record ?')) return false;
        
        jQuery.ajax({
            type:"post",
            url:"<?= admin_url('admin-ajax.php'); ?>",
            dataType:"json",
            data:{
                action:"delete_cage_record",
                cage_id,
                "_wpnonce": "<?= wp_create_nonce('delete_cage_record'); ?>"
            },
            beforeSend:function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success:function(data){
                if(data.status=="success"){
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                }
                jQuery(ref).attr('disabled', false);
            }
        });
    }

    (function($){
        $(document).ready(function(){

            $('#extendCageAlertForm').validate({
                rules: {
                    pickup_date: "required",
                    office_notes: "required",
                }
            });
            
            $('#addCageNotesForm').validate({
                rules: {
                    notes: "required",
                }
            });

            $('#extendCageAlertForm').on('submit', function(e){
                e.preventDefault();

                jQuery.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    dataType: "json",
                    data: $(this).serialize(),
                    beforeSend: function(){

                        jQuery('#submit_btn').attr('disabled', true);
                        jQuery(record_ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled',true);
                    },
                    success: function(data){
                        alert(data.message);

                        jQuery(record_ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled',false);
                        jQuery('#submit_btn').attr('disabled', false);
                        jQuery('#extendCageAlertForm').trigger('reset');
                        jQuery('#extendCageAlertModal').modal('hide');
                    }
                });

            });

            $('#addCageNotesForm').on('submit', function(e){
                e.preventDefault();

                $.ajax({
                    type:"post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data:$(this).serialize(),
                    dataType: "json",
                    beforeSend:function(){
                        $('#addNoteBtn').attr('disabled', true);
                    },
                    success:function(data){
                        alert(data.message);

                        if(data.status === "success"){
                            $('#addCageNotesForm').trigger('reset');
                            $('#addCageNoteModal').modal('hide');
                        }

                        $('#addNoteBtn').attr('disabled', false);
                    }
                })
            })

            $('#cageQuantityRetrievedForm').validate({
                rules: {
                    quantity_reterieved: "required",
                }
            });

        });
    })(jQuery);    
</script>

<?php
global $wpdb;

$conditions = [];

if (!current_user_can('other_than_upstate')) {
    $accessible_branches = (new Branches)->partner_accessible_branches(true);
    $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

    $conditions[] = " TD.branch_id IN ($accessible_branches)";
}

if (!empty($_GET['branch_id']) && $_GET['branch_id'] != "all") {
    $branch = esc_html($_GET['branch_id']);
    $conditions[] = " TD.branch_id='$branch'";
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';
$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;
$total_rows = $wpdb->get_var("
    select count(*)  
    from {$wpdb->prefix}vehilce_inspection VI
    left join {$wpdb->prefix}technician_details TD 
    on VI.technician_id=TD.id
    join {$wpdb->prefix}vehicles V
    on VI.vehicle_id=V.id
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);
$total_pages = $total_pages == 0 ? 1 : $total_pages;

$records = $wpdb->get_results("
    select VI.*, TD.first_name, TD.last_name, V.year, V.make, V.model, L.slug as branch_name
    from {$wpdb->prefix}vehilce_inspection VI
    left join {$wpdb->prefix}technician_details TD 
    on VI.technician_id=TD.id
    join {$wpdb->prefix}vehicles V
    on VI.vehicle_id=V.id
    left join {$wpdb->prefix}branches L
    on L.id = TD.branch_id
    $conditions
    order by VI.created_at desc
    LIMIT $offset, $no_of_records_per_page
");

$branches = (new Branches)->getAllBranches();
?>

<div class="container-fluid">
    <div class="row">
        <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Vehicle Condition Proof <small>(<?= $total_rows; ?> Records Found)</small></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Assigned to</th>
                                <th>Condition Proof</th>
                                <th>Vehicle Video</th>
                                <th>Branch</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($records) && count($records) > 0) : ?>
                                <?php foreach ($records as $record) : ?>
                                    <tr>
                                        <td><?= $record->year . " " . $record->make . " " . $record->model; ?></td>
                                        <td><?= $record->first_name . " " . $record->last_name; ?></td>
                                        <td><button class="btn btn-primary show_condition_proof" data-condition-proof='<?= $record->condition_pics; ?>'><span><i class="fa fa-eye"></i></span> Show</button></td>
                                        <?php if (empty($record->aws_video_key)) : ?>
                                            <td>-</td>
                                        <?php else : ?>
                                            <td><button onclick="viewVideo('<?= $record->aws_video_key; ?>')" class="btn btn-primary"><span><i class="fa fa-video-camera"></i></span> View</button></td>
                                        <?php endif; ?>

                                        <td><?= (new GamFunctions)->beautify_string($record->branch_name); ?></td>

                                        <td><?= !empty($record->date) ? date('d M Y h:i A', strtotime($record->created_at)) : ''; ?></td>
                                        <td>
                                            <label class="radio-inline"><input onclick="approveConditionProof(<?= $record->id; ?>, this)" value="approve" data-proof-id="<?= $record->id; ?>" type="radio" name="optradio_<?= $record->id; ?>" <?= $record->status == "approved" ? 'checked' : ''; ?>>Approve</label>

                                            <label class="radio-inline"><input onclick="openRejectModal(<?= $record->id; ?>, this)" value="reject" type="radio" name="optradio_<?= $record->id; ?>" <?= $record->status == "rejected" ? 'checked' : ''; ?>>Reject</label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7">No Recourd Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_template_part('template-parts/employee/training-material-popup');?>

<!-- Modal -->
<div id="condition_proof_docs" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Vehicle Condition Proofs</h4>
            </div>
            <div class="modal-body proof_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Reject Condition Modal -->
<div id="rejectConditionProof" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reject Condition Proof</h4>
            </div>
            <div class="modal-body">
                <form id="rejectConditionProofForm" action="">

                    <input type="hidden" name="proof_id">

                    <div class="form-group">
                        <label for="notes">Notes for technician</label>
                        <textarea name="notes" id="notes" cols="30" rows="5" class="form-control"></textarea>
                    </div>
                    <button class="btn btn-primary rejectSubmitBtn"><span><i class="fa fa-ban"></i></span> Reject Proof</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    let radio_btn_ref;

    function approveConditionProof(proof_id, ref) {
        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "approve_reject_vehicle_condition_proof",
                status: "approve",
                proof_id,
                "_wpnonce": "<?= wp_create_nonce('approve_reject_vehicle_condition_proof'); ?>"
            },
            success: function(data) {

                if (data.status !== "success") {
                    alert(data.message);
                    jQuery(ref).prop('checked', false);
                }

            }
        });
    }

    function rejectConditionProof(proof_id, notes) {

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data: {
                action: "approve_reject_vehicle_condition_proof",
                status: "reject",
                notes,
                proof_id,
                "_wpnonce": "<?= wp_create_nonce('approve_reject_vehicle_condition_proof'); ?>"
            },
            beforeSend: function() {
                jQuery('.rejectSubmitBtn').attr('disabled', true);
            },
            success: function(data) {

                alert(data.message);
                jQuery('.rejectSubmitBtn').attr('disabled', false);

                if (data.status === "success") {
                    jQuery(radio_btn_ref).prop('checked', true);
                    jQuery('#rejectConditionProofForm').trigger('reset');
                    jQuery('#rejectConditionProof').modal('hide');
                }
            }
        });
    }

    function openRejectModal(proof_id, ref) {
        radio_btn_ref = ref;
        jQuery(ref).prop('checked', false);
        jQuery('#rejectConditionProofForm input[name="proof_id"]').val(proof_id);
        jQuery('#rejectConditionProof').modal('show');
    }

    (function($) {
        $(document).ready(function() {

            $('#rejectConditionProofForm').validate({
                rules: {
                    notes: "required"
                },
                submitHandler: function() {
                    const notes = $('#rejectConditionProofForm textarea[name="notes"]').val();
                    const proof_id = $('#rejectConditionProofForm input[name="proof_id"]').val();

                    rejectConditionProof(proof_id, notes);

                    return false;
                }
            });

            $('.approve_reject_mileage_proof').on('click', function() {

                const status = $(this).val();
                const proof_id = $(this).attr('data-proof-id');

                // call ajax to update status 
                $.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data: {
                        action: "approve_reject_vehicle_condition_proof",
                        status,
                        proof_id,
                        "_wpnonce": "<?= wp_create_nonce('approve_reject_vehicle_condition_proof'); ?>"
                    },
                    success: function(data) {
                        console.log(data);
                    }
                });

            });

            $('.show_condition_proof').on('click', function() {

                const proof_data = $(this).attr('data-condition-proof');
                const proof_html = generateDocsHtml(proof_data);

                $('.proof_body').html(proof_html);
                $('#condition_proof_docs').modal('show');

            });

        });
    })(jQuery);
</script>
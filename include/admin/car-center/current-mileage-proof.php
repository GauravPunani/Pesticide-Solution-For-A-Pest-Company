<?php

global $wpdb;

$conditions = [];

if (!current_user_can('other_than_upstate')) {
    $accessible_branches = (new Branches)->partner_accessible_branches(true);
    $accessible_branches = "'" . implode("', '", $accessible_branches) . "'";

    $conditions[] = " T.branch_id IN ($accessible_branches)";
}

if (isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id'] != "all") {
    $branch = esc_html($_GET['branch_id']);
    $conditions[] = " T.branch_id = '$branch'";
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';
$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page = 50;
$offset = ($pageno - 1) * $no_of_records_per_page;
$total_rows = $wpdb->get_var("
    select count(*) 
    from {$wpdb->prefix}vehicle_mileage VM 
    left join {$wpdb->prefix}technician_details T 
    on VM.technician_id=T.id
    join {$wpdb->prefix}vehicles V
    on VM.vehicle_id=V.id
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);
$total_pages = $total_pages == 0 ? 1 : $total_pages;

$records = $wpdb->get_results("
    select VM.*,T.first_name,T.last_name,V.year,V.make,V.model  
    from {$wpdb->prefix}vehicle_mileage VM 
    left join {$wpdb->prefix}technician_details T 
    on VM.technician_id=T.id
    join {$wpdb->prefix}vehicles V
    on VM.vehicle_id=V.id
    $conditions
    order by VM.date desc 
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
                    <h3 class="card-title">Current Mileage Proof</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Assigned to</th>
                                <th>Mileage</th>
                                <th>Mileage Proof</th>
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
                                        <td><?= $record->mileage; ?> Miles</td>
                                        <?php if (!empty($record->proof_pic)) : ?>
                                            <td><a target="_blank" class="btn btn-primary" href="<?= $record->proof_pic;  ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <?php else : ?>
                                            <td><b>Not Found</b></td>
                                        <?php endif; ?>
                                        <td><?= !empty($record->date) ? date('d M Y', strtotime($record->date)) : ''; ?></td>
                                        <td>
                                            <label class="radio-inline"><input value="approve" class="approve_reject_mileage_proof" data-mileage="<?= $record->mileage; ?>" data-proof-id="<?= $record->id; ?>" type="radio" name="optradio_<?= $record->id; ?>" <?= $record->status == "approved" ? 'checked' : ''; ?>>Approve</label>
                                            <label class="radio-inline"><input data-mileage="<?= $record->mileage; ?>" data-proof-id="<?= $record->id; ?>" value="reject" class="approve_reject_mileage_proof" type="radio" name="optradio_<?= $record->id; ?>" <?= $record->status == "rejected" ? 'checked' : ''; ?>>Reject</label>
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


<script>
    (function($) {
        $('.approve_reject_mileage_proof').on('click', function() {
            let status = $(this).val();
            let proof_id = $(this).attr('data-proof-id');
            let mileage = $(this).attr('data-mileage');

            // call ajax to update status 
            $.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                data: {
                    action: "approve_reject_mileage_proof",
                    status: status,
                    mileage: mileage,
                    proof_id: proof_id,
                    "_wpnonce": "<?= wp_create_nonce('approve_reject_mileage_proof'); ?>"
                },
                success: function(data) {
                    console.log(data);
                }
            })

        })
    })(jQuery);
</script>
<?php
global $wpdb;
try {
    $contract = $wpdb->get_row("select * from {$wpdb->prefix}maintenance_contract where id='{$_GET['contract_id']}'");
    if (!$contract) {
        echo "<h1>No Record Found</h1>";
        exit;
    }
} catch (Exception $e) {
    echo  "<h1>Somthing went wrong,please try again later</h1>";
    exit;
}
$upload_dir = wp_upload_dir();
$branches = (new Branches)->getAllBranches();
?>


<div class="row">
    <div class="col-md-offset-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <?php (new GamFunctions)->getFlashMessage(); ?>
                <h3 class="page-header text-center">Edit Maintenance Contract</h3>

                <form id="editContractForm" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">

                    <?php wp_nonce_field('update_monthly_quarterly_contract'); ?>

                    <input type="hidden" name="action" value="update_monthly_quarterly_contract">
                    <input type="hidden" name="contract_id" value="<?= $contract->id; ?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <table class="table table-striped">

                        <tr>
                            <th>Client Location</th>
                            <td>
                                <div class="form-group">
                                    <select class="form-control" name="branch_id">
                                        <option value="">Select</option>
                                        <?php if (!empty($branches) && count((array)$branches) > 0) : ?>
                                            <?php foreach ($branches as $branch) : ?>
                                                <option value="<?= $branch->id; ?>" <?= $branch->id == $contract->branch_id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>Client Name</th>
                            <td><input class="form-control" type="text" name="client_name" value="<?= $contract->client_name; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Address</th>
                            <td><input type="text" class="form-control" name="client_address" value="<?= $contract->client_address; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Phone No.</th>
                            <td><input class="form-control" type="text" name="client_phone_no" value="<?= $contract->client_phone_no; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Email</th>
                            <td><input class="form-control" type="text" name="client_email" value="<?= $contract->client_email; ?>"></td>
                        </tr>
                        <tr>
                            <th>Cost Per Visit</th>
                            <td><input class="form-control" type="text" name="cost_per_month" value="<?= $contract->cost_per_month; ?>"></td>
                        </tr>
                        <tr>
                            <th>Total Cost</th>
                            <td><input class="form-control" type="text" name="total_cost" value="<?= $contract->total_cost; ?>"></td>
                        </tr>
                        <tr>
                            <th>Contract Start Date</th>
                            <td><input class="form-control" type="date" name="contract_start_date" value="<?= $contract->contract_start_date; ?>"></td>
                        </tr>
                        <tr>
                            <th>Contract End Date</th>
                            <td><input class="form-control" type="date" name="contract_end_date" value="<?= $contract->contract_end_date; ?>"></td>
                        </tr>
                        <tr>
                            <th>Contract Type</th>
                            <td>
                                <select class="form-control" name="type">
                                    <option value="">Select</option>
                                    <option value="monthly" <?= $contract->type == "monthly" ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="quarterly" <?= $contract->type == "quarterly" ? 'selected' : ''; ?>>Quarterly</option>
                                </select>
                        </tr>
                        <tr>
                            <th>Client Notes</th>
                            <td>
                                <textarea name="notes_for_client" id="" cols="90" rows="5" class="form-control"><?= $contract->client_notes; ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update</button>
                            </td>
                        </tr>
                    </table>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        $(document).ready(function() {
            $('#editContractForm').validate({
                rules: {
                    client_name: "required",
                    client_address: "required",
                    client_phone_no: "required",
                    client_email: "required",
                    cost_per_month: "required",
                    total_cost: "required",
                    contract_start_date: "required",
                    contract_end_date: "required",
                    branch_id: "required"
                }
            })
        })
    })(jQuery);
</script>
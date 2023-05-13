<?php
global $wpdb;
try {
    $contract = $wpdb->get_row("select * from {$wpdb->prefix}commercial_maintenance where id='{$_GET['contract_id']}'");
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
                <h3 class="page-header text-center">Edit Commercial Maintenance Contract</h3>

                <form id="editContractForm" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <?php wp_nonce_field('update_commercial_contract'); ?>
                    <input type="hidden" name="action" value="update_commercial_contract">
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
                            <th>Establishment Name</th>
                            <td><input class="form-control" type="text" name="establishement_name" value="<?= $contract->establishement_name; ?>"></td>
                        </tr>
                        <tr>
                            <th>Person In Charge</th>
                            <td><input class="form-control" type="text" name="person_in_charge" value="<?= $contract->person_in_charge; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Address</th>
                            <td><textarea class="form-control" name="client_address" id="" cols="30" rows="5"><?= $contract->client_address; ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Establishment Phone No.</th>
                            <td><input class="form-control" type="text" name="establishment_phoneno" value="<?= $contract->establishment_phoneno; ?>"></td>
                        </tr>
                        <tr>
                            <th>Res. Per. In Charge Phone No.</th>
                            <td><input class="form-control" type="text" name="res_person_in_charge_phone_no" value="<?= $contract->res_person_in_charge_phone_no; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Email</th>
                            <td><input class="form-control" type="text" name="client_email" value="<?= $contract->client_email; ?>"></td>
                        </tr>
                        <tr>
                            <th>Cost Per Vist</th>
                            <td><input class="form-control" type="text" name="cost_per_visit" value="<?= $contract->cost_per_visit; ?>"></td>
                        </tr>
                        <tr>
                            <th>Frequency of Visit</th>
                            <td><input class="form-control" type="text" name="frequency_of_visit" value="<?= $contract->frequency_of_visit; ?>"></td>
                        </tr>
                        <tr>
                            <th>Frequency Per</th>
                            <td><input class="form-control" type="text" name="frequency_per" value="<?= $contract->frequency_per; ?>"></td>
                        </tr>
                        <tr>
                            <th>Prefered Time</th>
                            <td><input class="form-control" type="time" name="prefered_time" value="<?= $contract->prefered_time; ?>"></td>
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
        $('#editContractForm').validate({
            rules: {
                branch_id: 'required',
                establishement_name: 'required',
                person_in_charge: 'required',
                client_address: 'required',
                establishment_phoneno: 'required',
                res_person_in_charge_phone_no: 'required',
                client_email: 'required',
                cost_per_visit: 'required',
                frequency_of_visit: 'required',
                frequency_per: 'required',
                prefered_time: 'required',
                contract_start_date: 'required',
                contract_end_date: 'required',
            }
        })
    })(jQuery);
</script>
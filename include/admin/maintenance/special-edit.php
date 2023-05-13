<?php 
global $wpdb;
try{
    $contract=$wpdb->get_row("select * from {$wpdb->prefix}special_contract where id='{$_GET['contract_id']}'");
    if(!$contract){
        echo "<h1>No Record Found</h1>";
        exit;
    }
}
catch(Exception $e){
    echo  "<h1>Somthing went wrong,please try again later</h1>";
    exit;
}
$upload_dir=wp_upload_dir();
$branches = (new Branches)->getAllBranches();
?>

<div class="row">
    <div class="col-md-offset-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <?php (new GamFunctions)->getFlashMessage(); ?>
                <h3 class="page-header text-center">Edit Special Maintenance Contract</h3>                

                <form id="editContractForm" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">

					<?php wp_nonce_field('update_special_contract'); ?>

                    <input type="hidden" name="action" value="update_special_contract">
                    <input type="hidden" name="contract_id" value="<?= $contract->id; ?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <table class="table table-striped">
                        <tr>
                            <th>Client Location</th>
                            <td>
                                <div class="form-group">
                                    <select class="form-control" name="branch_id">
                                        <option value="">Select</option>
                                        <?php if(!empty($branches) && count((array)$branches)>0): ?>
                                            <?php foreach($branches as $branch): ?>
                                                <option value="<?= $branch->id; ?>" <?= $branch->id == $contract->branch_id ? 'selected': ''; ?>><?= $branch->location_name; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Service Type</th>
                            <td>
                                <select name="service_type">
                                    <option value="">Select</option>
                                    <option value="Monthly" <?= $contract->service_type=="Monthly" ? 'selected': ''; ?>>Monthly</option>
                                    <option value="Bi-Monthly" <?= $contract->service_type=="Bi-Monthly" ? 'selected': ''; ?>>Bi-Monthly</option>
                                    <option value="As needed service within 90 days of initial service" <?= $contract->service_type=="As needed service within 90 days of initial service" ? 'selected': ''; ?>>As needed service within 90 days of initial service</option>
                                </select>
                        </tr>
                        <tr>
                            <th>Cost</th>
                            <td><input class="form-control" type="text" name="cost" value="<?= $contract->cost; ?>"></td>
                        </tr>
                        <tr>
                            <th>Days</th>
                            <td><input class="form-control" type="text" name="days" value="<?= $contract->days; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Name</th>
                            <td><input class="form-control" type="text" name="client_name" value="<?= $contract->client_name; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Address</th>
                            <td><textarea class="form-control" name="client_address" id="" cols="30" rows="5"><?= $contract->client_address; ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Client Phone No.</th>
                            <td><input class="form-control" type="text" name="client_phone" value="<?= $contract->client_phone; ?>"></td>
                        </tr>
                        <tr>
                            <th>Client Email</th>
                            <td><input class="form-control" type="text" name="client_email" value="<?= $contract->client_email; ?>"></td>
                        </tr>
                        <tr>
                            <th>From Date</th>
                            <td><input type="date" name="from_date" value="<?= $contract->from_date; ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>To Date</th>
                            <td><input type="date" name="to_date" value="<?= $contract->to_date; ?>" class="form-control"></td>
                        </tr>
                        <tr>
                            <th>Client Notes</th>
                            <td>
                                <textarea name="notes_for_client" id="" cols="90" rows="5" class="form-control"><?= $contract->notes; ?></textarea>
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
    (function($){
        $(document).ready(function(){
            $('#editContractForm').validate({
                rules: {
                    branch_id: "required",
                    service_type: "required",
                    cost: "required",
                    days: "required",
                    client_name: "required",
                    client_address: "required",
                    client_phone: "required",
                    client_email: "required",
                    from_date: "required",
                    from_date: "required",
                }
            })
        })
    })(jQuery);
</script>
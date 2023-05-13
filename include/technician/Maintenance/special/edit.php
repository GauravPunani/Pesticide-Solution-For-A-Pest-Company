<?php 

global $wpdb;
$contract=$wpdb->get_row("select * from {$wpdb->prefix}special_contract where id='{$_GET['maintenance-id']}'");
if(!$contract){
    echo "<h1>No Record Found</h1>";
    exit;
}

$callrail_traking_numbers=(new Callrail_new)->get_all_tracking_no();

$upload_dir=wp_upload_dir();
$branches = (new Branches)->getAllBranches();
?>

<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <div class="card">
            <div class="card-head">
                <h4 class="text-center">Edit Special Maintenance Contract</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                    <input type="hidden" name="action" value="update_special_contract">
                    <input type="hidden" name="contract_id" value="<?= $contract->id; ?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <table class="table table-striped">
                        <tr>
                            <th>Client Location</th>
                            <td>
                                <div class="form-group">
                                    <select class="form-control" name="client_location">
                                        <option value="">Select</option>
                                        <?php if(!empty($branches) && count((array)$branches)>0): ?>
                                            <?php foreach($branches as $branch): ?>
                                                <option value="<?= $branch->id; ?>" <?= $branch->id == $contract->branch_id ? 'selected': ''; ?>><?= $branch->branch_id; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Service Type</th>
                            <td>
                                <select class="form-control" name="service_type">
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
                            <td><input class="form-control" type="date" name="from_date" value="<?= $contract->from_date; ?>"></td>
                        </tr>
                        <tr>
                            <th>To Date</th>
                            <td><input class="form-control" type="date" name="to_date" value="<?= $contract->to_date; ?>"></td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td><input class="form-control" type="date" name="date_created" value="<?= date('Y-m-d',strtotime($contract->date_created)); ?>"></td>
                        </tr>
                        <tr>
                            <th>Contract Pdf</th>
                            <?php if(!empty($contract->pdf_path)): ?>
                                <td><a target="_blank" href="<?= $upload_dir['baseurl'].$contract->pdf_path; ?>" class="btn btn-info"><span><i class="fa fa-eye"></i></span> View</a></td>
                            <?php else: ?>
                                <td><p class="text-danger">Not Available</p></td>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <th>Callrail Tracking Number</th>
                            <td>
                                <?php if(is_array($callrail_traking_numbers) && count($callrail_traking_numbers)>0): ?>
                                    <select name="callrail_id" class="form-control">
                                    <option value="">Select</option>
                                    <?php foreach($callrail_traking_numbers as $key=>$val): ?>
                                        <option value="<?= $val->id; ?>" <?= $contract->callrail_id==$val->id ? "selected" : '';  ?> ><?= $val->tracking_phone_no; ?> - <?= $val->tracking_name; ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                <?php endif ?>
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
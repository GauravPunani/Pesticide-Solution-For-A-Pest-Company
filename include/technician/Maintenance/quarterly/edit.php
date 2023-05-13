<?php 

global $wpdb;
    
$contract=$wpdb->get_row("select * from {$wpdb->prefix}maintenance_contract where id='{$_SESSION['quarterly_maintenance_editable']['id']}'");

$callrail_traking_numbers=(new Callrail_new)->get_all_tracking_no();


// echo "<pre>";print_r($contract);wp_die();
if(!$contract){
    echo "<h1>No Record Found</h1>";
    exit;
}

$upload_dir=wp_upload_dir();
$locations=(new GamFunctions)->get_all_locations();
?>

<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <div class="card">
            <div class="card-head">
                <h4 class="text-center">Edit Quarterly Maintenance Contract</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                    <input type="hidden" name="action" value="update_monthly_quarterly_contract">
                    <input type="hidden" name="contract_id" value="<?= $contract->id; ?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <table class="table table-striped">
                        <tr>
                            <th>Client Location</th>
                            <td>
                                <div class="form-group">
                                    <select class="form-control" name="client_location">
                                        <option value="">Select</option>
                                        <?php if(!empty($locations) && count((array)$locations)>0): ?>
                                            <?php foreach($locations as $location): ?>
                                                <option value="<?= $location->slug; ?>" <?= $location->slug==$contract->client_location ? 'selected': ''; ?>><?= $location->location_name; ?></option>
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
                            <td><textarea class="form-control" name="client_address" id="" cols="30" rows="5"><?= $contract->client_address; ?></textarea></td>
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
                            <th>Cost Per Month</th>
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
                                    <option value="monthly" <?= $contract->type=="monthly" ? 'selected':''; ?>>Monthly</option>
                                    <option value="quarterly" <?= $contract->type=="quarterly" ? 'selected':''; ?>>Quarterly</option>
                                </select>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td><input class="form-control" type="date" name="date" value="<?= date('Y-m-d',strtotime($contract->date)); ?>"></td>
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
                                    <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Contract</button>
                                </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>    
    </div>
</div>
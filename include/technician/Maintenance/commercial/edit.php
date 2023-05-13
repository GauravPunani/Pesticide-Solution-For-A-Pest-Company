<?php 

global $wpdb;
$contract=$wpdb->get_row("select * from {$wpdb->prefix}commercial_maintenance where id='{$_GET['maintenance-id']}'");
if(!$contract){
    echo "<h1>No Record Found</h1>";
    exit;
}
$callrail_traking_numbers=(new Callrail_new)->get_all_tracking_no();

$upload_dir=wp_upload_dir();
$branches = (new Branches)->getAllBranches();
?>

<div class="row">
    <div class="col-md-offset-3 col-md-6">
        <div class="card">
            <div class="card-head">
                <h4 class="text-center">Edit Commercial Maintenance Contract</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                    <input type="hidden" name="action" value="update_commercial_contract">
                    <input type="hidden" name="contract_id" value="<?= $contract->id; ?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <table class="table table-striped">
                        <tr>
                            <th>Client Location</th>
                            <td>
                                <div class="form-group">
                                    <select class="form-control" name="branch_i">
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
                            <th>Date</th>
                            <td><input class="form-control" type="date" name="date_created" value="<?= date('Y-m-d',strtotime($contract->date_created)); ?>"></td>
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
                            <th>Contract Pdf</th>
                            <?php if(!empty($contract->pdf_path)): ?>
                                <td><a target="_blank" href="<?= $upload_dir['baseurl'].$contract->pdf_path; ?>" class="btn btn-info"><span><i class="fa fa-eye"></i></span> View</a></td>
                            <?php else: ?>
                                <td><p class="text-danger">Not Available</p></td>
                            <?php endif; ?>
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
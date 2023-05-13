<?php

$cold_caller_id = (new ColdCaller)->getLoggedInColdCallerId();
$employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
$bria_license_key = (new Bria)->getLicenseKeyByEmployeeId($employee_id);
$available_keys = (new Bria)->getkeys(true);

?>

<div class="card full_width table-responsive">
    <div class="card-body">
        <form class="res-form" action="<?= admin_url('admin-post.php'); ?>" method="post">
            <h3 class="page-header text-center">Link Bria License Key</h3>

            <?php wp_nonce_field('link_bria_key_by_employee'); ?>

            <input type="hidden" name="action" value="link_bria_key_by_employee">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

            <?php if($bria_license_key): ?>
                <p><b>Linked License Key: </b> <?= $bria_license_key->title." : ".$bria_license_key->key; ?></p>
            <?php else: ?>
                <p class="text-danger">No Bria License Key linked to your account yet</p>
            <?php endif; ?>
            <?php if(is_array($available_keys) && count($available_keys) > 0): ?>
            <div class="form-group">
                <label for="">Select from available keys</label>
                <select name="license_key_id" class="form-control select2-field">
                    <option value="">Select</option>
                    
                        <?php foreach($available_keys as $available_key): ?>
                            <option value="<?= $available_key->id; ?>"><?= $available_key->title." : ".$available_key->key; ?></option>
                        <?php endforeach; ?>
                </select>
            </div>

            <p><b>Note : </b> Any old bira license key assigned to your account will be unlinked as soon as you link a new key. You can only link one key with your account at at time.</p>

            <button class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Update Bria License Key</button>
            <?php else: ?>
                <p class="text-danger">No available licesnse key right now in system to be linked   .</p>
            <?php endif; ?>
        </form>
    </div>
</div>
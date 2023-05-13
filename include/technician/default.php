<?php

global $wpdb;
$user = $args['user'];

$dashboard_url = site_url()."/technician-dashboard";

// GET TECHNICIAN ID 
$technician_id = $user->id;

// GET EMPLOYEE FROM TECHNCIAN ID 
$employee_id = (new Employee\Employee)-> getEmployeeIdByRefId($technician_id, 1);
if($employee_id) $employee = (new Employee\Employee)->getEmployee($employee_id);

// GET VEHICLE ID 
$vehicle_id = $user->vehicle_id;

// IF VEHICLE ID FOUND
if(!empty($vehicle_id)){
    // GET TECHNICIAN VEHICLE DATA 
    $vehicleData = (new CarCenter)->getVehicleById($vehicle_id);
    if(!$vehicleData) (new CarCenter)->requestForVehicle($technician_id);
}
else{
    $vehicleData = false;
    (new CarCenter)->requestForVehicle($technician_id);
}

// GET THE NOTICES FOR THE TECHNICIAN IF ANY

$critical_notices = (new Notices)->getAccountNotices($technician_id, 'critical');
$normal_notices = (new Notices)->getAccountNotices($technician_id, 'normal');

// if any vehicle is linked and it's not confirmed in last 30 days 
$confirm = false;
if($vehicleData){

    $last_confirmed = $wpdb->get_var("
        select last_confirmed
        from {$wpdb->prefix}vehicles    
        where id='$vehicle_id' 
    ");

    if(empty($last_confirmed)) $confirm = true;
    
    if(!empty($last_confirmed) && strtotime(date('Y-m-d',strtotime($last_confirmed))) < strtotime('-30 days'))
        $confirm = true;

    if($confirm) (new CarCenter)->requestForVehicleVerification($technician_id);

    $isPesticideDecalPending = (new Technician_Details)->isPesticideDecalPending($technician_id, $user->branch_id, $vehicleData->pesticide_decal);
    
    if($isPesticideDecalPending) (new CarCenter)->requestForPesticideDecalProof($technician_id);

    $isPendingMileageInformation = false;
    if(empty($vehicleData->last_break_change_mileage) || empty($vehicleData->last_oil_change_mileage) || empty($vehicleData->current_mileage)) {
        $isPendingMileageInformation = true;
        (new CarCenter)->requestForMileageRelatedInformation($technician_id);
    }
    
}

(new GamFunctions)->getFlashMessage();
?>

<h3>Welcome <b><?= $user->first_name; ?> <?= $user->last_name; ?></b></h3>

<div class="notice notice-error">
    <p><b>Periodically Required Proofs</b></p>
    <ul>
        <li>You're required to upload vehicle condition proof every 1st and 14th of the month. </li>
        <li>You're required to upload current mileage proof every 1st and 14th of the month.</li>
    </ul>
</div>

<div class="notice notice-info">
    <p><b>Required Car Center Proofs</b></p>
    <ul>
        <li>You're required to upload oil change proof at every 5k within 5 days of time.</li>
        <li>You're required to upload break pad change proof at every 15k within 5 days of time.</li>
        <li>You're required to upload car wash proof every 1st of month within 2 days of time.</li>
    </ul>
</div>

<?php if($vehicleData): ?>

    <div class="notice notice-info">
        <p>You're currently reporting that you're driving vehicle <b><?= $vehicleData->year." ".$vehicleData->make." ".$vehicleData->model; ?></b> with plate no <b><?= $vehicleData->plate_number; ?></b>. If you need to change this car, please <a target="_blank" href="<?= $dashboard_url; ?>/?view=vehicle-details&cnw=true">click here</a></p>
    </div>
    

    <?php if($isPesticideDecalPending): ?>
        <h4 class="page-header"><strong>Pesticide Decal Proof</strong></h4>
        <div class="notice notice-error">
            <p>Please upload your pesticide decal proof in system by <a href="<?= $dashboard_url; ?>/?view=pesticide-decal-proof">Clicking here.</a> If you don't have pesticide decal on vehicle, please request the same from office first.</p>
        </div>
    <?php endif; ?>

    <?php if($isPendingMileageInformation): ?>
        <?php get_template_part('/include/technician/car-center/mileage-fields-form',null,['data' => $vehicleData]); ?>
    <?php endif; ?>
	
	<?php if(empty($vehicleData->parking_address)): ?>
        <?php get_template_part('/include/technician/car-center/parking-address-form',null,['data' => $vehicleData]); ?>
    <?php endif; ?>    

<?php else: ?>
    <h4 class="page-header"><b>Vehicle Error</b></h4>
    <div class="notice notice-error">
        <p>No vehicle found linked to your profile, please add vehicle by <a href="<?= $dashboard_url; ?>/?view=vehicle-details">clicking here</a></p>
    </div>
<?php endif; ?>

<!-- CRITICAL ERROR NOTICES  -->
<?php if(is_array($critical_notices) && count ($critical_notices)>0): ?>
    <h4 class="page-header"><strong>Critical Issues</strong> <small><i>(Needs to be resolved in order to unlock account)</i></small></h4>
    <?php foreach($critical_notices as $notice): ?>
        <?php if($notice->level=="critical"): ?>
            <div class="notice notice-error">
                <p>
                <?= $notice->notice; ?>
                <?php if($notice->type=="account_freezed_by_office"): ?>
                    <small><i>(After resolving this issue, please contact office to unlock your account)</i></small>
                <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php if($confirm): ?>
    <?php get_template_part('/include/technician/car-center/confirm-vehicle'); ?>
<?php endif; ?>

<?php if(empty($user->independent_contractor) || empty($user->non_competes)): ?>     
    <h4 class="page-header"><strong>Agreement Notice</strong></h4>
    <div class="notice notice-error">
       <p>All technicians are required to fill & sign the contract/agreement form. Please <a href="/technician-contract" target="_blank">Click here</a> to go to the page and fill the details.</p> 
    </div>
<?php endif; ?>

<?php 
    if(!empty($employee)) get_template_part('include/employees/templates/notices', null, ['user' => $employee]);
?>

<!-- SYSTEM GENERATED NOTICES  -->
<?php if(is_array($normal_notices) && count ($normal_notices)>0): ?>
    <h4 class="page-header"><strong>System Generated Notices</strong></h4>
    <?php foreach($normal_notices as $notice): ?>
        <?php if($notice->level=="normal"): ?>
            <div class="notice notice-info">
                <p><?= $notice->notice; ?></p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- IF IT'S 6 PM , THEN ASK FOR NOTES  -->
<?php if((int) date('h') >= 18): ?>
    <div class="page-header"><strong>Pending Notices</strong></div>
    <div class="notice notice-info">
        <p><span><i class="fa fa-clock"></i> Please submit your notes for today</span></p>
    </div>
<?php endif; ?>
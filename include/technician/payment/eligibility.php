<?php

if(!isset($args['user'])) return;

$technician_id = $args['user']->id;
$employee_id = (new Employee\Employee)->getEmployeeIdByRefId($technician_id, 1);

if(!$employee_id) return;

$week = date('Y')."-W".(date('W') - 1);

list($status, $reason) = (new EmployePayment)->isEligibleForPay($employee_id, $week);
?>

<h3 class="page-header">Payment Eligibility (last week)</h3>

<?php if($status):  ?>
    <p class="text-success">You're eligible for last week payment.</p>
<?php else: ?>
    <div class="notice notice-error">
        <p><b>Please note that you might be ineligible for payment due to current week account lock reasons as well. There could be several other factors as well for payment ineligibility although the final decision is always made by office. This is just for the purpose of technician to clear any important notice from his account to make sure of timely payments.</b></p>
        <p class="text-danger">You're not eligible for last week payment because of following reasons:</p>
        <p class="text-danger"><?= $reason ?></p>        
    </div>

<?php endif; ?>
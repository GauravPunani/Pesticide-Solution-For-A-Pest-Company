<?php

global $wpdb;

$employee = $args['user'];

if($employee->role_id == '1'){
    $type = "technician";
}elseif($employee->role_id == '2'){
    $type = "coldcallers";
}elseif($employee->role_id == '3'){
    $type = "office";
}elseif($employee->role_id == '4'){
    $type = "door";
}

$all_notices = $wpdb->get_results("
    select * from 
    {$wpdb->prefix}notice 
    where type = 'all'
");

$noticeByTypes = $wpdb->get_results("
    select * from 
    {$wpdb->prefix}notice 
    where type = '$type'
");

if(!empty($employee)){
$single_notices = $wpdb->get_results("
    select Notice.*, EmployeeNotices.*, Employee.name as emp_name
    from {$wpdb->prefix}notice Notice
    inner join {$wpdb->prefix}employee_notice EmployeeNotices 
    on Notice.id=EmployeeNotices.notice_id
    left join {$wpdb->prefix}employees Employee
    on EmployeeNotices.employee_id=Employee.id
    where EmployeeNotices.employee_id = '$employee->id'
    ");
}

?>

<h3>Welcome <b><?= @$employee->name; ?></b></h3>

<!-- OFFICE TECHNICIAN NOTICES  -->
<?php if(is_array($all_notices) && count($all_notices)>0): ?>
    
    <?php foreach($all_notices as $notice): ?>
        <?php if($notice->type=="all"): ?>
            <div class="notice notice-success">
                <p><span><i class="fa fa-exclamation"></i></span> <?= $notice->notice; ?></p>    
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php if(is_array($noticeByTypes) && count($noticeByTypes)>0): ?>
    <?php foreach($noticeByTypes as $not): ?>
            <div class="notice notice-success">
                <p><span><i class="fa fa-user"></i></span> <?= $not->notice; ?></p>    
            </div>
    <?php endforeach; ?>
<?php endif; ?>


<!-- OFFICE TECHNICIAN NOTICES  -->
<?php if(isset($single_notices) && is_array($single_notices) && count($single_notices)>0): ?>
    <?php foreach($single_notices as $notice): ?>
        <?php if($notice->type=="single"): ?>
            <div class="notice notice-error">
                <p><span><i class="fa fa-exclamation-triangle"></i></span> <?= $notice->notice; ?></p>    
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
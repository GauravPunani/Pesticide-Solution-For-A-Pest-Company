<?php

$cold_caller_id = $args['user']->id;
$employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_id, 2);
$roles = (new ColdCallerRoles)->getColdCallerRoles($employee_id);

?>
<h3 class="page-header">Assigned Roles</h3>
<?php if(is_array($roles) && count($roles) >0): ?>
    <ul>
        <?php foreach($roles as $roles): ?>
            <li><?= $roles->role_name; ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="text-danger">No role assigned to you yet.</p>
<?php endif; ?>
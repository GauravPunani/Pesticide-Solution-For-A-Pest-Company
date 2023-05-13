<?php

$employee_id = (new Employee\Employee)->__getLoggedInEmployeeId();

$roles = (new Roles)->getRoles($employee_id);
?>

<h3 class="page-header">Assigned Roles</h3>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Role</th>
            <th>Assigned At</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($roles as $role): ?>
            <tr>
                <td><?= $role->name; ?></td>
                <td><?= date('d M Y h:i:A', strtotime($role->created_at)); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php

(new Navigation)->coldCallerRolesNavigation(@$_GET['tab']);

if(empty($_GET['tab'])) return get_template_part('include/admin/employees/cold-caller/roles/roles');

switch ($_GET['tab']) {
    case 'available-roles':
        get_template_part('include/admin/employees/cold-caller/roles/roles');
    break;
    case 'assigned-roles':
        get_template_part('include/admin/employees/cold-caller/roles/assigned-roles');
    break;
    case 'cold-caller-types':
        get_template_part('include/admin/employees/cold-caller/roles/cold-caller-types');
    break;
    
    default:
        get_template_part('include/admin/employees/cold-caller/roles/roles');    
    break;
}
<?php

(new Navigation)->officeRolesNavigation(@$_GET['tab']);

if(empty($_GET['tab'])) return get_template_part('/include/admin/employee-roles/office-roles');

switch($_GET['tab']){
    case 'roles':
        return get_template_part('/include/admin/employee-roles/office-roles');
    break;
    case 'linked_employees':
        return get_template_part('/include/admin/employee-roles/linked-employees');
    break;
    default:
        return get_template_part('/include/admin/employee-roles/office-roles');
    break;
}




<?php

(new Navigation)->gam_employees();
(new Navigation)->office_staff(@$_GET['tab']);

if(!isset($_GET['tab']) || empty($_GET['tab'])){
    return get_template_part('/include/admin/employees/office-staff/staff-members');
}

$tab = esc_html($_GET['tab']);

switch ($tab) {
    case 'staff-members':
    case 'fired':
    case 'pending-verification':
    case 'inactive':
        get_template_part('/include/admin/employees/office-staff/staff-members');
    break;
        
    case 'signup':
        get_template_part('template/employees/office-staff/signup');
    break;
    
    default:
        get_template_part('/include/admin/employees/office-staff/staff-members');    
    break;
}
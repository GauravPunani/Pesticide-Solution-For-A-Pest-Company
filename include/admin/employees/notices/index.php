<?php

(new Navigation)->employee_notices(@$_GET['tab']);

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'add-notices':
            get_template_part('/include/admin/employees/notices/add-notice');
        break;
    
        case 'view-notices':
            get_template_part('/include/admin/employees/notices/view-notices');
        break;

        case 'critical-notices':
            get_template_part('/include/admin/technician-notices/critical-notices');
        break;
        
        default:
            get_template_part('/include/admin/employees/notices/add-notice');    
        break;
    }
}
else{
    get_template_part('/include/admin/employees/notices/add-notice');
}
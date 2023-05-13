<?php
    (new Navigation)->technician_dashboard_navigation(@$_GET['tab']);
    
    if(isset($_GET['tab']) && !empty($_GET['tab'])){
        switch ($_GET['tab']) {

            case 'all-notices':
                get_template_part('/include/admin/technician/all-notices');    
            break;

            case 'add-new':
                get_template_part('/include/admin/technician/add-notice');    
            break;

            case 'critical-notices':
                get_template_part('/include/admin/technician-notices/critical-notices');
            break;
            
            default:
                get_template_part('/include/admin/technician/all-notices');    
            break;
        }
    }
    else{
        get_template_part('/include/admin/technician/all-notices');    
    } 

?>

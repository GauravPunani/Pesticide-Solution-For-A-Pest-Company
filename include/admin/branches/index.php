<?php

(new Navigation)->branches_navigation(@$_GET['tab']);

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'test':
        case 'all-branches':
        case 'active-branches':
            
        case 'inactive-branches':
            get_template_part('/include/admin/branches/branches');
        break;

        case 'create-branch':
            get_template_part('/include/admin/branches/create-branch');
        break;
        
        default:
            get_template_part('/include/admin/branches/branches');
        break;
    }
}
else{
    get_template_part('/include/admin/branches/branches');
}
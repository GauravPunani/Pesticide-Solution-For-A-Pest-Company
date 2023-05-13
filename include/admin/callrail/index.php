<?php
(new Navigation)->callrail_listing_tabs(@$_GET['tab']);

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {

        case 'create-tracker':
            get_template_part('/include/admin/callrail/create-tracker');
        break;

        case 'unattributed-trackers':
            get_template_part('/include/admin/callrail/callrail-unknown-listing');
        break;

        case 'all-callrail-trackers':
            get_template_part('/include/admin/callrail/callrail-listing');
        break;
        
        default:
            get_template_part('/include/admin/callrail/callrail-listing');    
        break;
    }
}
else{
    get_template_part('/include/admin/callrail/callrail-listing');
}
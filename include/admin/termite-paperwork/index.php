<?php
(new Navigation)->termite_navigation(@$_GET['tab']);

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'termite-certificate':
            get_template_part('/include/admin/termite-paperwork/termite-certificate');            
        break;
        case 'termite-graph':
            get_template_part('/include/admin/termite-paperwork/termite-graph');            
        break;
        case 'florida-wood-inspection':
            get_template_part('/include/admin/termite-paperwork/florida-wood-inspection');            
        break;
        case 'florida-consumer-consent':
            get_template_part('/include/admin/termite-paperwork/florida-consumer-consent');            
        break;

        case 'npma-33':
            get_template_part('/include/admin/termite-paperwork/npma-33');            
        break;
        
        default:
            get_template_part('/include/admin/termite-paperwork/termite-certificate');
        break;
    }
}
else{
    get_template_part('/include/admin/termite-paperwork/termite-certificate');
}
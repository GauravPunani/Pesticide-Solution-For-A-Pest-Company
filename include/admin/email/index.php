<?php 
(new Navigation)->email_database_navigation(@$_GET['tab']);

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'cold-calls':
            get_template_part('/include/admin/email/clients');
        break;

        case 'view-call-logs':
            get_template_part('/include/admin/email/view-call-logs');
        break;

        case 'non-reocurring':
            get_template_part('/include/admin/email/clients');
        break;
        
        case 'reocurring':
            get_template_part('/include/admin/email/clients');
        break;

        case 'all-clients':
            get_template_part('/include/admin/email/clients');
        break;

        case 'reocurring':
            get_template_part('/include/admin/email/edit-email');
        break;


        default:
            get_template_part('/include/admin/email/clients');
        break;
    }
} else {
    get_template_part('/include/admin/email/clients');
}


<?php
    (new Navigation)->reimbursement_tabs(@$_GET['tab']);
    if(isset($_GET['tab']) && !empty($_GET['tab'])){
        switch ($_GET['tab']) {

            case 'reimbursement-log':
                get_template_part('include/admin/reimbursement/reimbursement-log');
            break;
            
            default:
                get_template_part('include/admin/reimbursement/reimbursement');
            break;
        }
    }
    else{
        get_template_part('include/admin/reimbursement/reimbursement');
    }
?>
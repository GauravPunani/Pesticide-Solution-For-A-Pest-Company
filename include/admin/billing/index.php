<?php

$branch_id = empty($_GET['branch_id']) ? 1 : $_GET['branch_id'];

(new Navigation)->location_tabs($branch_id);

(new Navigation)->billing_tabs($_GET['page'],@$_GET['tab']);


if(isset($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'billing':
            $billings=(new Autobilling)->get_unpaid_invoices($branch_id,true,false,'',true)->group_unpaid_invoices();
            get_template_part('/include/admin/billing/clients-billing',null,['data'=>$billings]);
        break;
        case '30_days_due':
            $billings=(new Autobilling)->get_unpaid_invoices($branch_id,true,true,'',true)->group_unpaid_invoices();
            get_template_part('/include/admin/billing/clients-billing',null,['data'=>$billings]);
        break;
        case 'in_collection':
            $billings=(new Autobilling)->get_unpaid_invoices($branch_id,true,false,'collection')->group_unpaid_invoices();
            get_template_part('/include/admin/billing/clients-in-collections',null,['data'=>$billings]);
        break;
        case 'no_email':
            get_template_part('/include/admin/billing/no-email');
        break;
        case 'mini_statement_log':
            get_template_part('/include/admin/billing/mini-statement-log');
        break;
        case 'error_log':
            get_template_part('/include/admin/billing/error-log');
        break;
        case 'billing_no_email':
            get_template_part('/include/admin/billing/billing-no-email');
        break;
        
        default:
            $billings=(new Autobilling)->get_unpaid_invoices($branch_id,true,false,'',true)->group_unpaid_invoices();
            get_template_part('/include/admin/billing/clients-billing',null,['data'=>$billings]);
        break;
    }
}
else{
    $billings=(new Autobilling)->get_unpaid_invoices($branch_id,true,false,'',true)->group_unpaid_invoices();
    get_template_part('/include/admin/billing/clients-billing',null,['data'=>$billings]);
}
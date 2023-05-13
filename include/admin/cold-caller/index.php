<?php

(new Navigation)->gam_employees();
(new Navigation)->cold_caller_navigation(@$_GET['tab']);

if(empty($_GET['tab'])) return get_template_part('/include/admin/cold-caller/cold-callers');

$tab = sanitize_text_field($_GET['tab']);

switch ($tab){
    case 'leads':
        get_template_part('/include/admin/cold-caller/leads');
    break;
    case 'create-lead':
        get_template_part('/include/admin/cold-caller/create-lead');
    break;
    case 'active-cold-callers':
    case 'inactive-cold-callers':
    case 'fired-cold-callers':
    case 'pending-verification':
        get_template_part('/include/admin/cold-caller/cold-callers');
    break;
    case 'performance':
        get_template_part('/include/admin/cold-caller/performance');
    break;
    case 'score_board':
        get_template_part('/include/admin/cold-caller/score_board');
    break;
    case 'pending-payments':
        get_template_part('include/admin/cold-caller-pay/pending-payments');
    break;
    case 'payment-calculation':
        get_template_part('include/admin/cold-caller-pay/payment-calculation');
    break;
    case 'proof-of-payment':
        get_template_part('include/admin/cold-caller-pay/proof-of-payment');
    break;
    case 'payment_structure':
        get_template_part('include/admin/cold-caller-pay/payment-structure');
    break;
    case 'pending-verification':
        get_template_part('include/admin/cold-caller/pending-verification');
    break;

    default:
        get_template_part('/include/admin/cold-caller/cold-callers');
    break;
}
?>
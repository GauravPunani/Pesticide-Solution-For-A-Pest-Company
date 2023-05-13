<?php

(new Navigation)->gam_employees();
(new Navigation)->technician_listing_tabs(@$_GET['view']);


if(!isset($_GET['view']) || empty($_GET['view'])){
    return get_template_part('/include/admin/technician/gam-technicians');
}

$view = $_GET['view'];

switch ($view) {

    case 'pending-applications':
    case 'rejected-applications':
    case 'fired-technician':
    case 'resigned-technician':
        get_template_part('/include/admin/technician/gam-technicians');
    break;
    
    case 'pending-payments':
        get_template_part('include/admin/technician-pay/pending-payments');
    break;

    case 'payment-calculation':
        get_template_part('include/admin/technician-pay/payment-calculation');
    break;

    case 'proof-of-payment':
        get_template_part('include/admin/technician-pay/proof-of-payment');
    break;

    case 'payment_structure':
        get_template_part('include/admin/technician-pay/payment-structure');
    break;
    
    default:
        get_template_part('/include/admin/technician/gam-technicians');    
    break;
}
<?php

(new Navigation)->gam_employees();
(new Navigation)->doorToDoorSales(@$_GET['tab']);

if(!isset($_GET['tab']) || empty($_GET['tab'])){
    return get_template_part('/include/admin/employees/door-to-door-sales/sales-persons');
}

$tab = esc_html($_GET['tab']);

switch ($tab) {
    case 'sales-persons':
    case 'pending-application':
    case 'fired-sales-persons':
    case 'inactive-sales-persons':
        get_template_part('/include/admin/employees/door-to-door-sales/sales-persons');
    break;
    default:
        get_template_part('/include/admin/employees/door-to-door-sales/sales-persons');    
    break;
}
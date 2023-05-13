<?php

(new Navigation)->employeePaymentNavigation();

(new Navigation)->parkingTickets(@$_GET['tab']);

if(!isset($_GET['tab']) || empty($_GET['tab'])){
    get_template_part('include/admin/employees/parking-tickets/tickets-list');
    return;
}

switch ($_GET['tab']) {
    case 'ticket-list':
        get_template_part('include/admin/employees/parking-tickets/tickets-list');        
    break;

    case 'create-ticket':
        get_template_part('include/admin/employees/parking-tickets/create-ticket');        
    break;

    case 'completed-ticket':
        get_template_part('include/admin/employees/parking-tickets/completed-ticket');        
    break;

    default:
        get_template_part('include/admin/employees/parking-tickets/tickets-list');
    break;
}

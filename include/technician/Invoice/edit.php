<?php

global $wpdb;

if (!isset($_SESSION['invoice_editable']['id']) || empty($_SESSION['invoice_editable']['id'])) {
    echo "Something Went Wrong";
    wp_die();
}

$invoice = $wpdb->get_row("
select * from 
{$wpdb->prefix}invoices 
where id = '{$_SESSION['invoice_editable']['id']}' 
");

get_template_part('/include/admin/invoice/invoice-edit');
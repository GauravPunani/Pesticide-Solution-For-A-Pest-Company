<?php


if(!isset($_GET['tab']) || empty($_GET['tab'])) return get_template_part('include/admin/bria/license-keys');

switch ($_GET['tab']) {
    case 'license-key':
        get_template_part('include/admin/bria/license-keys');
    break;
    
    default:
        get_template_part('include/admin/bria/license-keys');
    break;
}
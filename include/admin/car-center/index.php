<?php
global $wpdb;

(new Navigation)->car_center(@$_GET['tab']); 

if(empty($_GET['tab'])) return get_template_part('/include/admin/car-center/vehicles');

$tab = sanitize_text_field($_GET['tab']);

switch ($tab) {
    case 'mileage-proof':
        get_template_part('/include/admin/car-center/current-mileage-proof');
    break;
    case 'oil-change-proof':
        get_template_part('/include/admin/car-center/last-oil-change-proof');
    break;
    case 'break-pads-change-proof':
        get_template_part('/include/admin/car-center/break-pad-proof');
    break;
    case 'vehicle-condition-proof':
        get_template_part('/include/admin/car-center/vehicle-condition-proof');
    break;
    case 'car-wash-proof':
        get_template_part('/include/admin/car-center/car-wash-proof');
    break;

    case 'link-vehicle':
        get_template_part('/include/admin/car-center/link-vehicle');
    break;

    case 'create-vehicle':
        get_template_part('/include/admin/car-center/create-vehicle');
    break;

    default:
        get_template_part('/include/admin/car-center/vehicles');
    break;
    
}
   
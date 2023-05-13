<?php

// (new Navigation)->employee_notices(@$_GET['tab']);


if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        
        default:
            get_template_part('/include/admin/employees/attendence/view-list');    
        break;
    }
}
else{
    get_template_part('/include/admin/employees/attendence/view-list');
}
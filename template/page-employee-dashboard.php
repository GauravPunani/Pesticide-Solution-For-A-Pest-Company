<?php

/* Template Name: Employee Dashboard*/
get_header('technician');

// get employee and employee role
$employee = (new Employee\Employee)->getLoggedInEmployee();

// base on role redirect to different dashboard page
if($employee->role_id == 3) get_template_part('/include/employees/office-staff/dashboard');
if($employee->role_id == 4) get_template_part('/include/employees/door-to-door-sale/dashboard');

get_footer('technician');?>

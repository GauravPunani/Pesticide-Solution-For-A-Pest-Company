<?php  
    $employee = (new Employee\Employee)->getLoggedInEmployee();
    // print_r($_SESSION);
    if(isset($_SESSION['redirect_to_attendance'] ) && !empty($_SESSION['redirect_to_attendance'] )){
        get_template_part('include/employees/templates/attendance', null, ['user' => $employee]);
    }else {
        get_template_part('include/employees/templates/notices', null, ['user' => $employee]);
    }
?>
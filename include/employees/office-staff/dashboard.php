<?php  

$employee = (new Employee\Employee)->getLoggedInEmployee();
//pdie($employee);
?>
<div class="container-fluid">
    <h3 class="page-header text-center">Office Staff Dashboard</h3>
    <div class="col-md-2">
        <?php get_template_part('include/employees/office-staff/sidebar-navigation'); ?>
    </div>
    <div class="col-md-10">
        <?php (new GamFunctions)->getFlashMessage(); ?>

        <?php 
            if(isset($_GET['view'])){
                switch ($_GET['view']) {

                    case 'dashboard':
                        get_template_part('include/employees/templates/notices', null, ['user' => $employee]);
                    break;

                    case 'training-videos':
                        get_template_part('template-parts/employee/training-material');
                    break;
                    case 'view-task':
                        get_template_part('include/employees/templates/task/view-task');
                    break;
                    case 'assigned-roles':
                        get_template_part('include/employees/office-staff/assigned-roles/assigned-roles', null, ['user' => $employee]);
                    break;
                    case 'reimbursement':
                        get_template_part('include/technician/reimbursement/reimbursement-proof',null,['user'=> $employee]);
                    break;
                    case 'pending-reimbursement':
                        get_template_part('include/technician/reimbursement/pending-reimbursement',null,['user'=> $employee]);
                    break;
                    case 'reimbursed':
                        get_template_part('include/technician/reimbursement/reimbursed',null,['user'=> $employee]);
                    break;
                    case 'attendance':
                        get_template_part('include/employees/templates/attendance', null, ['user' => $employee]);
                    break;    

                    case 'mark-attendance':
                        get_template_part('include/employees/templates/mark-attendance', null, ['user' => $employee]);
                    break;   
                  
                    default:
                        get_template_part('include/employees/office-staff/index', null, ['user' => $employee]);
                    break;
                }

            }
            else{
                get_template_part('include/employees/office-staff/index', null, ['user' => $employee]);
            }
        ?>
            
    </div>
</div>
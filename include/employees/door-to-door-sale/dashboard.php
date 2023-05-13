<?php  

$employee = (new Employee\Employee)->getLoggedInEmployee();
?>

<div class="container-fluid">
    <div class="col-md-3 sidebar__navigation">
        <?php get_template_part('include/employees/door-to-door-sale/sidebar-navigation'); ?>
    </div>
    <div class="col-md-9 body__content">
        <?php (new GamFunctions)->getFlashMessage(); ?>

        <?php 
            if(isset($_GET['view'])){
                switch ($_GET['view']) {
                    case 'view-task':
                        get_template_part('include/employees/templates/task/view-task');
                    break;
                    case 'training-material':
                        get_template_part('template-parts/employee/training-material');
                    break;
                    default:
                    get_template_part('include/employees/templates/notices', null, ['user' => $employee]);
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
                }

            }
            else{
                get_template_part('include/employees/templates/notices', null, ['user' => $employee]);
            }
        ?>
            
    </div>
</div>
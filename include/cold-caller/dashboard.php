<?php  

$employee = (new Employee\Employee)->getLoggedInEmployee();

?>
<div class="container-fluid">
    <h3 class="page-header text-center">Cold Caller Dashboard</h3>
    <div class="col-md-2">
        <?php get_template_part('include/cold-caller/sidebar-navigation'); ?>
    </div>
    <div class="col-md-10">
        <?php (new GamFunctions)->getFlashMessage(); ?>

        <?php 
            if(isset($_GET['view'])){
                switch ($_GET['view']) {
                    case 'training-videos':
                        get_template_part('template-parts/cold-caller/training-material');
                    break;
               
                    case 'attendance':
                        get_template_part('include/cold-caller/templates/attendance', null, ['user' => $employee]);
                    break;    

                    case 'mark-attendance':
                        get_template_part('include/cold-caller/templates/mark-attendance', null, ['user' => $employee]);
                    break;   

                    default:
                        get_template_part('include/cold-caller/index', null, ['user' => $employee]);
                    break;
                }

            }
            else{
                get_template_part('include/cold-caller/index', null, ['user' => $employee]);
            }
        ?>
            
    </div>
</div>
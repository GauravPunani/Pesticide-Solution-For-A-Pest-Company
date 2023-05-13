<?php

/* Template Name: Cold Caller Dashbard */

get_header();

// get the user information 
global $wpdb;
$cold_caller_id = (new ColdCaller)->getLoggedInColdCallerId();
$user = (new ColdCaller)->getColdCallerById($cold_caller_id);
?>
<div class="container-fluid">
    <div class="col-md-3">
        <?php get_template_part('include/cold-caller/sidebar-navigation',null,['user'=>$user]); ?>
    </div>
    <div class="col-md-9">
        <?php (new GamFunctions)->getFlashMessage(); ?>
        <?php 
            if(isset($_GET['view'])){
                switch ($_GET['view']) {
                    case 'leads':
                        get_template_part('include/cold-caller/leads');
                    break;
					case 'create-lead':
                        get_template_part('include/cold-caller/create-lead');
                    break;
					case 'performance':
                        get_template_part('include/cold-caller/performance');
                    break;
					case 'scorecard':
                        get_template_part('include/cold-caller/scorecard');
                    break;
					case 'training-videos':
                        get_template_part('template-parts/employee/training-material');
                    break;
					case 'edit-profile':
                        get_template_part('include/cold-caller/edit-profile');
                    break;
					case 'additional-information':
                        get_template_part('include/cold-caller/profile/additional-information');
                    break;
                    case 'view-task':
                        get_template_part('include/employees/templates/task/view-task');
                        // get_template_part('include/cold-caller/tasks/view-task',null,['user'=>$user]);
                    break;
                    case 'update-bria-license-key':
                        get_template_part('include/cold-caller/bria-licsene-key', null, ['user'=>$user]);
                    break;
                    case 'roles':
                        get_template_part('include/cold-caller/roles', null, ['user'=>$user]);
                    break;
					
                    default:
                        get_template_part('include/cold-caller/index',null,['user'=>$user]);
                    break;
                }

            }
            else{
                get_template_part('include/cold-caller/index',null,['user'=>$user]);
            }
        ?>
            
    </div>
</div>

<?php

get_footer();?>



<?php
get_footer();

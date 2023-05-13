<?php

/* Template Name: Technician Dashboard*/

get_header('technician');

// get the user information 
global $wpdb;
$technician_id = (new Technician_details)->get_technician_id();
$technician = (new Technician_details)->getTechnicianById($technician_id);
?>


<div class="container-fluid">

    <div class="col-md-3 sidebar__navigation">
        <?php get_template_part('include/technician/sidebar-navigation',null,['user'=>$technician]); ?>
    </div>

    <div class="col-md-9 body__content">
        <?php if(isset($_SESSION['technician-success-notice'])): ?>
            <div class="alert alert-success"><?= $_SESSION['technician-success-notice']; ?></div>
        <?php unset($_SESSION['technician-success-notice']); ?>
        <?php endif; ?>

        <?php 
            if(!empty($_GET['view'])){
                switch ($_GET['view']) {
                    case 'calendar-events':
                        get_template_part('include/technician/calendar/index');
                    break;
                    case 'add-proof-of-deposit':
                        get_template_part('include/technician/deposit-proof/add-proof-of-deposit');
                    break;
                    case 'service-reports':
                        get_template_part('include/technician/service-reports', null, compact('technician'));
                    break;
                    case 'invoice':
                        get_template_part('include/technician/Invoice/listing',null,['user'=>$technician]);
                    break;
                    case 'residential-quote':
                        get_template_part('include/technician/Quotes/Residential/view',null,['user'=>$technician]);
                    break;
                    case 'commercial-quote':
                        get_template_part('include/technician/Quotes/Commercial/view',null,['user'=>$technician]);
                    break;
                    case 'monthly-maintenance':
                        get_template_part('include/technician/Maintenance/monthly/listing',null,['user'=>$technician]);
                    break;
                    case 'quarterly-maintenance':
                        get_template_part('include/technician/Maintenance/quarterly/listing',null,['user'=>$technician]);
                    break;
                    case 'special-maintenance':
                        get_template_part('include/technician/Maintenance/special/listing',null,['user'=>$technician]);
                    break;
                    case 'commercial-maintenance':
                        get_template_part('include/technician/Maintenance/commercial/listing',null,['user'=>$technician]);
                    break;
                    case 'daily-deposit-listing':
                        get_template_part('include/technician/deposit-proof/daily-deposit-listing',null,['user'=>$technician]);
                    break;
                    case 'reimbursement':
                        get_template_part('include/technician/reimbursement/reimbursement-proof',null,['user'=>$technician]);
                    break;
                    case 'pending-reimbursement':
                        get_template_part('include/technician/reimbursement/pending-reimbursement',null,['user'=>$technician]);
                    break;
                    case 'reimbursed':
                        get_template_part('include/technician/reimbursement/reimbursed',null,['user'=>$technician]);
                    break;
                    // case 'add-notes':
                    //     get_template_part('/include/technician/notes/add-notes',null,['user'=>$technician]);
                    // break;
                    // case 'view-notes':
                    //     get_template_part('include/technician/notes/view-notes',null,['user'=>$technician]);
                    // break;
                    // case 'add-special-notes':
                    //     get_template_part('/include/technician/special-notes/add-notes',null,['user'=>$technician]);
                    // break;
                    // case 'view-special-notes':
                    //     get_template_part('include/technician/special-notes/view-notes',null,['user'=>$technician]);
                    // break;
                    case 'profile':
                        get_template_part('include/technician/profile/profile-details',null,['user'=>$technician]);
                    break;
                    case 'edit-profile':
                        get_template_part('include/technician/profile/edit-profile',null,['user'=>$technician]);
                    break;
                    case 'mileage-proof':
                        get_template_part('include/technician/car-center/mileage-proof',null,['user'=>$technician]);
                    break;
                    case 'oil-change-proof':
                        get_template_part('include/technician/car-center/oil-change-proof',null,['user'=>$technician]);
                    break;
                    case 'vehicle-condition-proof':
                        get_template_part('include/technician/car-center/vehicle-condition-proof',null,['user'=>$technician]);
                    break;
                    case 'break-pad-proof':
                        get_template_part('include/technician/car-center/break-pad-proof',null,['user'=>$technician]);
                    break;
                    case 'car-wash-proof':
                        get_template_part('include/technician/car-center/car-wash-proof',null,['user'=>$technician]);
                    break;
                    case 'vehicle-details':
                        get_template_part('include/technician/car-center/vehicle-details',null,['user'=>$technician]);
                    break;
                    case 'view-task':
                        get_template_part('include/employees/templates/task/view-task');
                        // get_template_part('include/technician/task/view-task',null,['user'=>$technician]);
                    break;
                    case 'view-prices':
                        get_template_part('include/technician/material-prices',null,['user'=>$technician]);
                    break;
                    case 'resign':
                        get_template_part('include/technician/resign',null,['user'=>$technician]);
                    break;
                    // case 'add-prospectus':
                    //     get_template_part('include/technician/prospectus/add-prospectus',null,['user'=>$technician]);
                    // break;
                    // case 'view-prospectus':
                    //     get_template_part('include/technician/prospectus/view-prospectus',null,['user'=>$technician]);
                    // break;

                    case 'pesticide-decal-proof':
                        get_template_part('include/technician/car-center/pesticide-decal', null, ['user'=>$technician]);
                    break;
                    
                    case 'weekly-payment-proof':
                        get_template_part('include/technician/payment/weekly-payment-proof', null, ['user' => $technician]);
                    break;

                    case 'payment-eligibility':
                        get_template_part('include/technician/payment/eligibility', null, ['user' => $technician]);
                    break;

                    case 'salary-agreement':
                        get_template_part('include/technician/salary/tech-salary-agreements', null, ['user' => $technician]);
                    break;

                    case 'training-material':
                        get_template_part('template-parts/employee/training-material');
                    break;

                    default:
                        get_template_part('include/technician/default',null,['user'=>$technician]);
                    break;
                }

            }
            else{
                get_template_part('include/technician/default',null,['user'=>$technician]);
            }
        ?>
            
    </div>
    
</div>

<?php

get_footer('technician');?>

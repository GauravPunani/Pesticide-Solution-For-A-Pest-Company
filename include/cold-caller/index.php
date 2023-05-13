<?php
$cold_caller_data = $args['user'];
if ($cold_caller_data) {
    // GET EMPLOYEE FROM Cold CALLER REF ID
    $employee_id = (new Employee\Employee)->getEmployeeIdByRefId($cold_caller_data->id, 2);
    $employee = (new Employee\Employee)->getEmployee($employee_id);
}
// print_r($_SESSION);
?>


<h3 class="page-header">Cold Caller Dashboard</h3>

<?php
(new GamFunctions)->getFlashMessage();

if (isset($_SESSION['redirect_to_attendance']) && !empty($_SESSION['redirect_to_attendance'])) {
    get_template_part('include/cold-caller/templates/attendance', null, ['user' => $employee]);
} else {
    if (isset($_GET['view'])) {
        switch ($_GET['view']) {
            case 'attendance':
                get_template_part('include/cold-caller/templates/attendance', null, ['user' => $employee]);
                break;

            case 'mark-attendance':
                get_template_part('include/cold-caller/templates/mark-attendance', null, ['user' => $employee]);
                break;

            default:
                get_template_part('include/employees/templates/notices', null, ['user' => $employee]);
                break;
        }
    } else { ?>
        <p>Welcome <b><?= $args['user']->name; ?></b></p>
        <a target="_blank" href="?view=scorecard"><span><i class="fa fa-external-link"></i></span> See how your teammates are performing</a>
    <?php get_template_part('include/employees/templates/notices', null, ['user' => $employee]);
    }
}

if (empty($cold_caller_data->w9_pdf_path)) : ?>
    <?php get_template_part('/include/cold-caller/temp-fw9-form-details'); ?>
<?php endif; ?>
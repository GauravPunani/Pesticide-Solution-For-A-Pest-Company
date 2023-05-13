<?php

use phpDocumentor\Reflection\Types\Null_;

global $wpdb;

$conditions = [];

if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
    $employee_id = urldecode($_GET['employee_id']);
    $conditions[] = " employee_id = '$employee_id'";
}

if (isset($_GET['from_date']) && !empty($_GET['from_date'])) {
    $conditions[] = " DATE(Attendence.attendance_date) >= '{$_GET['from_date']}' ";
}

if (isset($_GET['to_date']) && !empty($_GET['to_date'])) {
    $conditions[] = " DATE(Attendence.attendance_date) <= '{$_GET['to_date']}' ";
}

if (isset($_GET['week']) && !empty($_GET['week'])) {
    $from_date = date('Y-m-d', strtotime('this monday', strtotime($_GET['week'])));
    $to_date = date(('Y-m-d'), strtotime('this sunday', strtotime($_GET['week'])));
    $conditions[] = " DATE(Attendence.attendance_date) >= '{$from_date}' ";
    $conditions[] = " DATE(Attendence.attendance_date) <= '{$to_date}' ";
}

if (count($conditions) > 0) {
    $conditions = (new GamFunctions)->generate_query($conditions);
} else {
    $conditions = "";
}


$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 20;
$offset = ($pageno - 1) * $no_of_records_per_page;

$total_rows = $wpdb->get_var("
    select Attendence.*
    from {$wpdb->prefix}attendance Attendence
    $conditions
    order by created_at desc
    LIMIT $offset, $no_of_records_per_page
");
$records_starting_index = (($pageno - 1) * $no_of_records_per_page) + 1;
$total_pages = ceil($total_rows / $no_of_records_per_page);
$attendences = $wpdb->get_results("
        select Attendence.*, Employee.username as emp_name, Employee.role_id, Employee.employee_ref_id, cold.name
        from {$wpdb->prefix}attendance Attendence
        left join {$wpdb->prefix}employees Employee
        on Attendence.employee_id=Employee.id AND Employee.employee_ref_id
        Left JOIN {$wpdb->prefix}cold_callers cold
        ON Attendence.employee_id  = cold.id
        $conditions
        order by created_at desc 
        LIMIT $offset, $no_of_records_per_page
    ");
$get_employees = (new Employee\Employee)->getAllEmployees(['office_staff']);
$get_cold_employees = (new Employee\Employee)->getAllEmployees(['cold_caller']);
?>

<?php if (!empty($_GET['employee_id']) || !empty($_GET['date'])) : ?>
    <p class="alert alert-success alert-dismissible">
        <a class="btn btn-info" href="<?= admin_url('admin.php?page=' . $_GET['page']); ?>">
            <span><i class="fa fa-database"></i></span> Show All Records
        </a>
    </p>
<?php else : ?>
    <p class="alert alert-info"><b><?= $total_rows; ?></b> Records Found</p>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="page-header"><span><i class="fa fa-filter"></i></span> Filter</h3>
                        <form action="">
                            <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                            <div class="form-group">
                                <label for="employees">Filter By Employee</label>
                                <select class="form-control select2-field" name="employee_id">
                                    <option value="">Select</option>
                                    <?php foreach ($get_employees as $emp) : ?>
                                        <option <?= (isset($_GET['employee_id']) && $_GET['employee_id'] == $emp->id) ? 'selected' : ""; ?> value="<?= urlencode($emp->id); ?>"><?= ucwords(str_replace('_', ' ', $emp->name)); ?></option>
                                    <?php endforeach; ?>

                                    <?php foreach ($get_cold_employees as $emp) : ?>
                                        <option <?= (isset($_GET['employee_id']) && $_GET['employee_id'] == $emp->employee_ref_id) ? 'selected' : ""; ?> value="<?= urlencode($emp->employee_ref_id); ?>"><?= ucwords(str_replace('_', ' ', $emp->name)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="radio-inline"><input type="radio" value="date" name="date_type" <?= (isset($_GET['date_type']) && $_GET['date_type'] == 'date') ? 'checked' : "checked"; ?>>By Date</label>
                                <label class="radio-inline"><input type="radio" <?= (isset($_GET['date_type']) && $_GET['date_type'] == 'week') ? 'checked' : ""; ?> value="week" name="date_type">By Week</label>
                            </div>

                            <div class="date_type_date <?= (isset($_GET['date_type']) && !empty($_GET['date_type']) && $_GET['date_type'] == 'week' ? 'hidden' : ''); ?>">
                                <div class="form-group">
                                    <label for="">From Date</label>
                                    <input type="date" value="<?= (isset($_GET['from_date']) && !empty($_GET['from_date']) ? $_GET['from_date'] : ''); ?>" name="from_date" class="form-control attendance_filter_input">
                                </div>

                                <div class="form-group">
                                    <label for="">To Date</label>
                                    <input type="date" value="<?= (isset($_GET['to_date']) && !empty($_GET['to_date']) ? $_GET['to_date'] : ''); ?>" name="to_date" class="form-control attendance_filter_input">
                                </div>
                            </div>

                            <div class="date_type_week <?= (isset($_GET['date_type']) && !empty($_GET['date_type']) && $_GET['date_type'] == 'week' ? '' : 'hidden'); ?>">
                                <div class="form-group">
                                    <label for="">Select Week</label>
                                    <input type="week" value="<?= (isset($_GET['week']) && !empty($_GET['week']) ? $_GET['week'] : ''); ?>" name="week" class="form-control attendance_filter_input">
                                </div>
                            </div>
                            <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Filter</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card full_width table-responsive">
                    <div class="card-body">
                        <button onclick="createAttend()" class="btn btn-primary pull-right"><span><i class="fa fa-plus"></i></span> Add Attendance</button>
                        <h4 class="card-title">All Employee Attendances</h4>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Start Time</th>
                                    <th>Close Time</th>
                                    <th>Attendance Date</th>
                                    <th>Total Hours</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (is_array($attendences) && count($attendences) > 0) : ?>
                                    <?php $attendence_hrs = $attendence_min = 0;
                                    foreach ($attendences as $attendence) : ?>
                                        <tr>
                                            <?php
                                            $emp_role_id = $attendence->role_id;
                                            $emp_name = $attendence->emp_name;
                                            $cold_call_name = $attendence->name;

                                            if (!empty($attendence)) {
                                                $t1 = $attendence->start_time;
                                                $t2 = $attendence->close_time;
                                            }
                                            $time1 = new DateTime($t1);
                                            $time2 = new DateTime($t2);

                                            $interval = $time1->diff($time2);

                                            $total_hours = $interval->format('%h') . " Hours " . $interval->format('%i') . " Minutes";

                                            if (!empty($interval->format('%h'))) {
                                                $attendence_hrs += $interval->format('%h');
                                            }

                                            if (!empty($interval->format('%i'))) {
                                                $attendence_min += $interval->format('%i');
                                            }
                                            ?>

                                            <td><?php if ($emp_role_id == '3') {  ?>
                                                    <?php echo $emp_name; ?>
                                                <?php } else {   ?>
                                                    <?php echo  $cold_call_name; ?>
                                                <?php } ?></td>
                                            <td><?= !empty($attendence->start_time) ? date('h:i A', strtotime($attendence->start_time)) : ''; ?></td>
                                            <td><?= !empty($attendence->close_time) ? date('h:i A', strtotime($attendence->close_time)) : ''; ?></td>
                                            <td><?= !empty($attendence->attendance_date) ? date('d M Y', strtotime($attendence->attendance_date)) : ''; ?></td>
                                            <td><?php if ($total_hours == "0 Hours 0 Minutes") {
                                                    echo "<p style='color: #bd1313;'>N/A</p>";
                                                } else {
                                                    echo $total_hours;
                                                } ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                    <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                        <li>
                                                            <a href="javascript:void(0)" data-emp-id='<?= $attendence->employee_id ?>' data-attend-id='<?= $attendence->id ?>' data-start='<?= $attendence->start_time ?>' data-close='<?= $attendence->close_time ?>' data-attend_date='<?= $attendence->attendance_date ?>' class="edit_attend"><span><i class="fa fa-edit"></i></span> Edit</a>
                                                        </li>
                                                        <li><a data-attendance-id="<?= $attendence->employee_id; ?>" data-attendance-date="<?= $attendence->attendance_date; ?>" class="view_reocurring_modal" data-toggle="modal" data-target="#reocurring_modal"><span><i class="fa fa-eye"></i></span> View</a></li>
                                                        <li>
                                                            <a onclick="deleteAttend(<?= $attendence->id; ?>,this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Attendence</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $records_starting_index++; ?>
                                    <?php endforeach; ?>
                                    <?php if (isset($_GET['date_type']) && !empty($_GET['date_type'])) : ?>
                                        <tr>
                                            <td colspan="4"><b>Total Time</b></td>
                                            <td colspan="2"><b><?= sprintf('%d Hours %d Minutes', $attendence_hrs, $attendence_min); ?></b></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4">No records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php (new GamFunctions)->render_pagination($pageno, $total_pages); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- View Attendance Log -->
<div id="reocurring_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><strong>View Attendance Status</strong></h4>
            </div>
            <div class="modal-body stu_data">
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
            </div>
        </div>
    </div>
</div>


<!-- EDIT Attendece MODAL -->
<div id="attendence_admin_edit_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Task</h4>
            </div>
            <div class="modal-body">
                <form id="edit_attendence_form" action="<?= admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('update_attendence_ea'); ?>
                    <input type="hidden" name="action" value="update_attendence_ea">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="emp_id" value="">
                    <input type="hidden" name="old_attend_date" value="">

                    <div class="form-group">
                        <label for="">Attendance Date</label>
                        <input type="date" name="attendance_date" max="<?= date("Y-m-d") ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="">Start Time</label>
                        <input type="time" name="start_time" required>
                    </div>

                    <div class="form-group">
                        <label for="">Close Time</label>
                        <input type="time" name="close_time">

                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Update Status</button>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end edit modal -->

<!-- CREATE ROLE MODAL  -->
<div id="createAttendModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Attendance</h4>
            </div>
            <div class="modal-body">
                <form id="createAttendForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                    <?php wp_nonce_field('create_attendence'); ?>
                    <input type="hidden" name="action" value="create_attendence">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <div class="form-group">
                        <label for="">Select Employee</label>
                        <select name="emp_id" class="form-control select2-field" required>
                            <option value="">Select</option>
                            <?php foreach ($get_employees as $emp) : ?>
                                <option value="<?= urlencode($emp->id); ?>"><?= ucwords(str_replace('_', ' ', $emp->name)); ?></option>
                            <?php endforeach; ?>

                            <?php foreach ($get_cold_employees as $emp) : ?>
                                <option value="<?= urlencode($emp->employee_ref_id); ?>"><?= ucwords(str_replace('_', ' ', $emp->name)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="">Attendance Date</label>
                        <input type="date" name="attendance_date" max="<?= date("Y-m-d") ?>">
                    </div>

                    <div class="form-group">
                        <label for="">Start Time</label>
                        <input type="time" name="start_time">
                    </div>

                    <div class="form-group">
                        <label for="">Close Time</label>
                        <input type="time" name="close_time">
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Role</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
<!-- End Create role -->


<script>
    function createAttend() {
        jQuery('#createAttendModal').modal('show');
    }

    function deleteNotice(notice_id, ref_elem) {
        if (confirm('Are you sure , you want to delete this notice ?')) {
            jQuery.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                data: {
                    action: 'delete_technician_dashboard_notice',
                    notice_id: notice_id,
                    "_wpnonce": "<?= wp_create_nonce('delete_technician_dashboard_notice'); ?>"
                },
                dataType: "json",
                success: function(data) {
                    if (data.status == "success") {
                        jQuery(ref_elem).parent().parent().fadeOut();
                    } else {
                        alert(data.message);
                    }
                }
            })
        }
    }


    (function($) {

        $(document).on('click', '.edit_attend', function(e) {
            var attend_id = $(this).attr('data-attend-id');
            var emp_id = $(this).attr('data-emp-id');
            var status = $(this).attr('data-task-status');
            var start = $(this).attr('data-start');
            var close = $(this).attr('data-close');
            var att_date = $(this).attr('data-attend_date');

            $('#edit_attendence_form input[name="id"]').val(attend_id);
            $('#edit_attendence_form input[name="emp_id"]').val(emp_id);
            $('#edit_attendence_form input[name="start_time"]').val(start);
            $('#edit_attendence_form input[name="close_time"]').val(close);
            $('#edit_attendence_form input[name="attendance_date"]').val(att_date);
            $('#edit_attendence_form input[name="old_attend_date"]').val(att_date);

            $('#attendence_admin_edit_modal').modal('show');
        });


        // View Attendance Log
        $(document).ready(function() {
            $('input[name="date_type"]').on('change', function() {
                let date_type = $(this).val();
                $('.attendance_filter_input').attr('value', '');
                if (date_type == "date") {
                    $('.date_type_date').removeClass('hidden');
                    $('.date_type_week').addClass('hidden');
                } else {
                    $('.date_type_date').addClass('hidden');
                    $('.date_type_week').removeClass('hidden');
                }

            })

            $('.view_reocurring_modal').on('click', function() {
                let attendance_id = $(this).attr('data-attendance-id');
                let attendance_date = $(this).attr('data-attendance-date');

                $.ajax({
                    type: "post",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data: {
                        action: "view_attendance_logs",
                        "_wpnonce": "<?= wp_create_nonce('view_attendance_logs'); ?>",
                        attendance_id: attendance_id,
                        attendance_date: attendance_date
                    },
                    beforeSend: function() {
                        $('#reocurring_modal .modal-body').html('<div class="loader"></div>');
                    },
                    success: function(data) {
                        $('#reocurring_modal .modal-body').html(data);
                    }
                })

            });
        });



    })(jQuery);


    function deleteAttend(attend_id) {

        if (confirm('Are you sure you want to delete this record?')) {
            jQuery.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                data: {
                    id: attend_id,
                    action: "delete_attendence",
                    "_wpnonce": "<?= wp_create_nonce('delete_attendence'); ?>"
                },
                dataType: "json",
                success: function(data) {
                    if (data.status == "success") {
                        location.reload();
                    } else {
                        alert('Something went wrong, please try again later');
                    }
                },
                error: function(request, status, error) {
                    // alert(request.responseText);
                    alert('Something went wrong, please try again later');

                }
            });
        }

    }
</script>
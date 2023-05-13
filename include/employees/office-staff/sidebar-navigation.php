<?php
    $user_id = $_SESSION['employee']['id'];

    if ($_SESSION['employee']['id']) {

        global $wpdb;
        $start_time = date("h:i a");
        $user_id = $_SESSION['employee']['id'];
    
        $staff_attendance = $wpdb->get_results("
                select *
                from {$wpdb->prefix}attendance
                where employee_id = '$user_id'
                ORDER BY id DESC LIMIT 0 , 1
            ");
        if ($staff_attendance){
            $staff = $staff_attendance[0]->employee_id;
            $atten_id = $staff_attendance[0]->id;
            $close_time1 = $staff_attendance[0]->close_time;
        }
    }


    $page_url = site_url() . "/employee-dashboard/";
?>

<ul class="nav nav-pills nav-stacked">
    <li class="active"><a href="<?= $page_url; ?>?view=dashboard"><span><i class="fa fa-dashboard"></i></span> Dashboard</a></li>
    <li><a href="<?= $page_url; ?>?view=training-videos"><span><i class="fa fa-video-camera"></i></span> Training Videos</a></li>
    <li><a href="?view=assigned-roles"><span><i class="fa fa-user"></i></span> Assigned Roles</a></li>
    <li><a href="?view=view-task"><span><i class="fa fa-tasks"></i></span> Task</a></li>
    <?php $employee_id = (new Employee\Employee)->__getLoggedInEmployeeId(); if(in_array($employee_id,[95,96])) : ?>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-exchange"></i></span> Reimbursement<span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="?view=reimbursement"><span><i class="fa fa-plus"></i></span> Add Reimbursement Proof</a></li>
            <li><a href="?view=pending-reimbursement"><span><i class="fa fa-clock-o"></i></span> Pending Reimbursement</a></li>
            <li><a href="?view=reimbursed"><span><i class="fa fa-exchange"></i></span> Reimbursed</a></li>
        </ul>
    </li>
    <?php endif;?>
    <li><a href="?view=attendance"><span><i class="fa fa-tasks"></i></span> Attendance</a></li>
    <!-- <li>< ?php get_template_part('include/employees/templates/logout-button'); ?></li> -->
    <li>
        <?php if (!empty($_GET['edit-id'])) { ?>
            <form action="<?= admin_url('admin-post.php'); ?>" method="post">

                <?php wp_nonce_field('logout_employee'); ?>

                <input type="hidden" name="action" value="logout_employee">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="user_id" value="<?= $atten_id; ?>">

                <button class="btn btn-default logout"><span><i class="fa fa-sign-out"></i></span> Logout</button>
            </form>
        <?php } else { ?>

            <form action="<?= admin_url('admin-post.php'); ?>" method="post">

                <?php wp_nonce_field('logout_employee'); ?>

                <input type="hidden" name="action" value="logout_employee">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="user_id" value="<?= $atten_id; ?>">

                <button class="btn btn-default log_out"><span><i class="fa fa-sign-out"></i></span> Logout</button>
            </form>
        <?php } ?>
    </li>
</ul>

<script type="text/javascript">
    let timer, currSeconds = 0;

    function resetTimer() {

        /* Hide the timer text */
        document.querySelector(".timertext")
        // .style.display = 'none';

        /* Clear the previous interval */
        clearInterval(timer);

        /* Reset the seconds of the timer */
        currSeconds = 0;

        /* Set a new interval */
        timer = setInterval(startIdleTimer, 60000);
    }

    // Define the events that
    // would reset the timer
    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer;
    window.ontouchstart = resetTimer;
    window.onclick = resetTimer;
    window.onkeypress = resetTimer;

    function startIdleTimer() {

        currSeconds++;
        if (currSeconds > 29) { // 20 minutes
            $('.logout, .log_out').trigger('click');
        }

    }
</script>
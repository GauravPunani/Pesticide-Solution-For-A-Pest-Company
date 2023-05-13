<?php
$user_id = $_SESSION['cold_caller_id'];

if($_SESSION['cold_caller_id']) {

    global $wpdb;
    $start_time = date("h:i a");
    $user_id = $_SESSION['cold_caller_id'];

    $staff_attendance = $wpdb->get_results("
        select *
        from {$wpdb->prefix}attendance
        where employee_id = '$user_id'
        ORDER BY id DESC LIMIT 0 , 1
    ");

    if($staff_attendance) {
        $staff = $staff_attendance[0]->employee_id; 
        $atten_id = $staff_attendance[0]->id; 
        $close_time1 = $staff_attendance[0]->close_time; 
    }
    $page_url = site_url()."/cold-caller-dashboard/";
}
// echo $user_id;
?>
<ul class="nav nav-pills nav-stacked">
    <li class="active"><a href="<?= site_url(); ?>/cold-caller-dashboard"><span><i class="fa fa-dashboard"></i></span> Dashboard</a></li>

    <li><a href="?view=training-videos"><span><i class="fa fa-video-camera"></i></span> Training Material</a></li>

    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-phone"></i></span> Cold Caller
            <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="?view=leads"><span><i class="fa fa-bullhorn"></i></span> Leads</a></li>
            <li><a href="?view=create-lead"><span><i class="fa fa-plus"></i></span> Create Lead</a></li>
            <li><a href="?view=performance"><span><i class="fa fa-bar-chart"></i></span> Performance</a></li>
            <li><a href="?view=scorecard"><span><i class="fa fa-star"></i></span> Scorecard</a></li>

        </ul>
    </li>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-user"></i></span> Profile
            <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="?view=edit-profile"><span><i class="fa fa-eye"></i></span> View Profile</a></li>
            <li><a href="?view=update-bria-license-key"><span><i class="fa fa-key"></i></span> Update Bria License Key</a></li>
            <li><a href="?view=add-update-roles"><span><i class="fa fa-list"></i></span> Add/Update Roles</a></li>
        </ul>
    </li>
    <li><a href="?view=roles"><span><i class="fa fa-list"></i></span> Roles</a></li>
    <li><a href="?view=view-task"><span><i class="fa fa-tasks"></i></span> Task</a></li>
    <li><a href="?view=attendance"><span><i class="fa fa-tasks"></i></span> Attendance</a></li>
    <li>
        <?php if (!empty($_GET['edit-id'])) { ?>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="logout_cold_caller">
                <input type="hidden" name="close_time" value="<?= $atten_id; ?>">
                <button class="btn btn-default logoutcoldcall"><span><i class="fa fa-sign-out"></i></span> Log Out</button>
            </form>
        <?php } else { ?>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="logout_cold_caller">
                <input type="hidden" name="close_time" value="<?= $atten_id; ?>">
                <button class="btn btn-default log_out_cold_call"><span><i class="fa fa-sign-out"></i></span> Log Out</button>
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
        timer = setInterval(startIdlecoldTimer, 60000);
    }

    // Define the events that
    // would reset the timer
    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer;
    window.ontouchstart = resetTimer;
    window.onclick = resetTimer;
    window.onkeypress = resetTimer;

    function startIdlecoldTimer() {

        currSeconds++;
        if (currSeconds > 29) { // 20 minutes
            $('.logoutcoldcall, .log_out_cold_call').trigger('click');
        }

    }
</script>
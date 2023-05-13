<?php 
    if($_GET['edit-id']) {
        $user_id = $_SESSION['cold_caller_id'];
        $employee_id = $_GET['edit-id'];
        $close_time = date("H:i:s");
        $staff_attendances = $wpdb->get_results("
            select *
            from {$wpdb->prefix}attendance
            where id = $employee_id
        ");
        $start = $staff_attendances[0]->start_time;
        $close = $staff_attendances[0]->close_time;
    }

?>
<?php error_reporting( E_ALL ); ?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="officeStaffattendanceForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                        <?php wp_nonce_field('update_attendance'); ?>
                        <input type="hidden" name="action" value="update_attendance">
                        <input type="hidden" name="page_url" value="/cold-caller-login/">
                        <input type="hidden" name="close_time" value="<?= date("h:i:s"); ?>">
                        <input type="hidden" name="user_id" value="<?= $user_id; ?>">
                        <input type="hidden" name="edit_id" value="<?= $employee_id; ?>">
                        <input type="hidden" name="attendance_date" value="<?= date('Y-m-d'); ?>">
                   
                        <div class="d-flex justify-content-start" style="background-color: #0000000f; padding: 13px;text-align: center;"> 
                            <label style="color:green;"><h2>Logged Out Time</h2></label>
                            <div class="form-group">
                                <div class="form-check">
                                    <h3><?php if($close_time=""){ echo $close_time; } ?></h3>
                                    <h3><?= date("h:i:s"); ?></h3>
                                </div>
                            </div>
                            <button class="btn btn-danger logoutbtn"><span><i class="fa fa-user-refresh"></i></span> LogOut</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
                $('.logoutbtn').trigger('click');
            } 
        }
    </script>



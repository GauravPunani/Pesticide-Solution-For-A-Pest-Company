<?php
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
?>
<?php error_reporting( E_ALL ); ?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <?php if ($user_id == !empty($staff) && $close_time1 == 0) { ?>

                        <?php (new GamFunctions)->getFlashMessage(); ?>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Started At</th>
                                        <th>Mark Attendance</th>
                                        <th>Day Close</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($staff_attendance) && count($staff_attendance) >0): ?>
                                        <?php foreach($staff_attendance as $staff_attendances): ?>
                                            <tr>
                                                <td><?= $staff_attendance[0]->start_time; ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <?php if($close_time1 == 0) { ?>
                                                        <button class="btn btn-success" >  Active</button>
                                                        <?php } else { ?> 
                                                        <button class="btn btn-danger " >  Stop</button> 
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                                <td><a href="<?= $page_url; ?>?view=mark-attendance&edit-id=<?= $atten_id; ?>"><button class="btn btn-primary dayclose" ><span><i class="fa fa-sign-out" style='font-size:21px'></i></span>  </button></a></td>   
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">No Record Found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>  
                        <?php } else { ?>
                                
                            <div class="d-flex justify-content-start" style="background-color: #0000000f; padding: 88px;text-align: center;"> 
                                    <h4>Mark Attendance :</h4>
                                    <form id="officeStaffEditForm" action="<?= admin_url('admin-post.php'); ?>" method="post">
                                        <?php wp_nonce_field('create_cold_attendance'); ?>
                                        <input type="hidden" name="action" value="create_cold_attendance">
                                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                        <input type="hidden" name="employee_id" value="<?= $user_id; ?>">
                                        <input type="hidden" name="start_time" value="<?= $start_time; ?>">
                                        <div class="dropdown">
                                            <button class="btn btn-danger"> Start</button>
                                        </div>
                                    </form>
                            </div>    
                        <?php } ?>    
                </div>
            </div>
        </div>
    </div>
</div>


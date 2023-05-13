<?php
$start_time = date("h:i a");

$staff_member_id = $args['member_id'];

$member_data = (new OfficeStaff)->getStaffMemberById($staff_member_id);


?>

<?php if($member_data): ?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Office Staff Attendance</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="officeStaffEditForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                        <!-- <?php wp_nonce_field('update_attendance'); ?> -->
                        <!-- <input type="hidden" name="action" value="update_attendance"> -->
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="account_id" value="<?= $member_data->id; ?>" >
                        <input type="hidden" name="employee_edit_id" value="3">
                        <input type="hidden" name="start_time" value="<?= $start_time; ?>">

                        <?php if($member_data->attendance == 1) { ?> 
                            <div class="d-flex justify-content-start" style="background-color: #0000000f; padding: 13px;text-align: center;"> 
                                <label style="color:green;"><h2>Loged In</h2></label>
                                <div class="form-group">
                                    <label for="role">Stating Time</label>
                                    <div class="form-check">
                                        <h3><?= date('h:i:sa', strtotime($staff_member->start_time)); ?></h3>
                                    </div>
                                </div>
                            </div>

                            <input class="form-check-input" type="hidden" name="attendance" id="attendance" value="0">
                            <button class="btn btn-primary"><span><i class="fa fa-user-refresh"></i></span> Day Close</button>
                            
                        <?php } else { ?>

                        <div class="form-group">
                            <label for="">Name</label>
                            <input type="text" class="form-control" name="name" value="<?= $member_data->name; ?>">
                        </div>

                        <div class="form-group">
                            <label for="role">Attendance</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="attendance" id="attendance" value="1" checked>
                                    <label class="form-check-label" for="attendance">Start</label>
                                </div>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-user-refresh"></i></span> Update</button>
                        </div><?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
    <h1>No Record Found</h1>
<?php endif; ?>

<script>
    (function($){
        $(document).ready(function(){
            $('#officeStaffEditForm').validate({
                rules:{
                    name: "required",
                    email: "required",
                    address: "required",
                    role: "required",                    
                }
            });
        });
    })(jQuery);
</script>
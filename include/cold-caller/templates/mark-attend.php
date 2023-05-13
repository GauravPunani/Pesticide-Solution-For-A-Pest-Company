<?php
$employee = $args['user'];
// print_r($employee);

$current_date = date('Y-m-d');

/* Get Attedence to check  */
$get_attendence = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}attendance WHERE employee_id = '$employee->id' AND DATE(created_at)='{$current_date}'
");

$check_attend = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}attendance WHERE employee_id = '$employee->id' AND DATE(created_at)='{$current_date}' AND close_time IS NOT NULL AND start_time IS NOT NULL
");


?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
        <?php (new GamFunctions)->getFlashMessage(); ?>
            <div class="col-sm-12 col-md-8">                            
                <div class="card full_width table-responsive" style="display:none;">
                    <div class="card-body">
                        <div class="attend-div">
                        <?php if(empty($check_attend)): ?>
                            <?php if(empty($get_attendence)): ?>
                                <button data-emp-id = '<?= $employee->id; ?>' data-tag = 'sign-in' onclick="markAttendence()" class="btn btn-primary pull-right mark-btn sign-in"><span><i class="fa fa-user"></i></span> Mark Sign In</button>
                            <?php else: ?>
                                <button data-emp-id = '<?= $employee->id; ?>' data-tag = 'sign-out' onclick="markAttendence()" class="btn btn-primary pull-right mark-btn"><span><i class="fa fa-user"></i></span> Mark Sign Out</button>
                            <?php endif; ?>
                        <?php endif; ?>

                        </div> 
                    </div>
                </div>
            </div>
        </div> 
    </div>
</div>

<script>

 

function markAttendence(){
    
    let emp_id =  $(".mark-btn").attr('data-emp-id');
    let tag =  $(".mark-btn").attr('data-tag');
    // console.log(emp_id);
        $.ajax({
            type:"post",
            url:"<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action:"mark_employee_attendence",
                emp_id:emp_id,
                tag:tag,
                "_wpnonce": "<?= wp_create_nonce('mark_employee_attendence'); ?>"
            },
            dataType:"json",
            beforeSend:function(){
                $('.mark-btn').append(' <i class="fa fa-circle-o-notch fa-spin"></i> ');
                $('.mark-btn').prop('disabled', true);
            },
            success:function(data){
                if(data.status == "success"){
                    location.reload()
                }else{
                    $('.mark-btn .fa.fa-spin').remove();
                    $('.mark-btn').prop('disabled', false);
                }
            }
        })

}

function attendance(){
    
    let emp_id =  $(".mark-btn").attr('data-emp-id');
    let tag =  $(".mark-btn").attr('data-tag');
    // console.log(emp_id);
        $.ajax({
            type:"post",
            url:"<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action:"create_attendence",
                emp_id:emp_id,
                tag:tag,
                "_wpnonce": "<?= wp_create_nonce('create_attendence'); ?>"
            },
            dataType:"json",
            beforeSend:function(){
                $('.mark-btn').append(' <i class="fa fa-circle-o-notch fa-spin"></i> ');
                $('.mark-btn').prop('disabled', true);
            },
            success:function(data){
                if(data.status == "success"){
                    location.reload()
                }else{
                    $('.mark-btn .fa.fa-spin').remove();
                    $('.mark-btn').prop('disabled', false);
                }
            }
        })

}

(function($){
let Now = new Date(),
  CurrentDay = Now.getDay(),
  OpeningTime = new Date(Now.getFullYear(), Now.getMonth(), Now.getDate(), 9, 00),
  Open = (Now.getTime() > OpeningTime.getTime());

if (Open) {
    $('.mark-btn.sign-in').css('display','block');
}else{
    $('.mark-btn.sign-in').css('display','none');
}
})(jQuery);

</script>
<?php

global $wpdb;

$employee_notices = $wpdb->get_results("
    select N.*, E.name as employee_name
    from {$wpdb->prefix}notice N
    left join {$wpdb->prefix}employee_notice EN
    on N.id = EN.notice_id
    left join {$wpdb->prefix}employees E
    on E.id = EN.employee_id
    order by N.created_at desc
");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Employee Notices</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Notice</th>
                                <th>Employee</th>
                                <th>Created at</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($employee_notices) && count($employee_notices)>0): ?>
                                <?php foreach($employee_notices as $notice): ?>
                                    <tr>
                                        <td><?= $notice->notice; ?></td>
                                        <td><?= empty($notice->employee_name) ? '<b>*All Employees</b>' : $notice->employee_name; ?></td>
                                        <td><?= !empty($notice->created_at) ? date('d M Y',strtotime($notice->created_at)) : ''; ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">                                            
                                                    <li><a onclick="deleteNotice(<?= $notice->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Notice</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No Notice found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteNotice(notice_id,ref_elem){
    if(confirm('Are you sure , you want to delete this notice ?')){
        jQuery.ajax({
            type:"post",
            url:"<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action:'emp_delete_notice',
                notice_id,
				"_wpnonce": "<?= wp_create_nonce('emp_delete_notice'); ?>"
            },
            dataType:"json",
            beforeSend:function(){
                jQuery(ref_elem).attr('disabled', true);
            },
            success:function(data){
                if(data.status=="success"){
                    jQuery(ref_elem).closest('.dropdown').parent().parent().remove();
                }
                else{
                    alert(data.message);
                    jQuery(ref_elem).attr('disabled', false);
                }
            }
        })
    }
}
</script>
<?php

global $wpdb;

if(isset($_GET['edit-notice-id']) && !empty($_GET['edit-notice-id'])){
    $notice_data=$wpdb->get_row("select * from {$wpdb->prefix}technician_dashboard_notices where id='{$_GET['edit-notice-id']}'");
    if($notice_data){
        get_template_part('/include/admin/technician/edit-notices',null,['data'=>$notice_data]);
        return;
    }

}


$notices=$wpdb->get_results("select * from {$wpdb->prefix}technician_dashboard_notices");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h4 class="card-title">All Technician Notices</h4>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Notice</th>
                                <th>Type</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($notices) && count($notices)>0): ?>
                                <?php foreach($notices as $notice): ?>
                                    <tr>
                                        <td><?= $notice->notice; ?></td>
                                        <td><?= (new GamFunctions)->beautify_string($notice->type); ?></td>
                                        <td><?= !empty($notice->date_created) ? date('d M Y',strtotime($notice->date_created)) : ''; ?></td>
                                        <td><a href="<?= $_SERVER['REQUEST_URI'];?>&edit-notice-id=<?= $notice->id; ?>" class="btn btn-primary"><span><i class="fa fa-edit"></i></span> Edit</a>
                                        <button onclick="deleteNotice('<?= $notice->id; ?>',this)" class="btn btn-danger"><span><i class="fa fa-trash"></i></span></button></td>
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
                action:'delete_technician_dashboard_notice',
                notice_id:notice_id,
				"_wpnonce": "<?= wp_create_nonce('delete_technician_dashboard_notice'); ?>"
            },
            dataType:"json",
            success:function(data){
                if(data.status=="success"){
                    jQuery(ref_elem).parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                }
            }
        })
    }
}
</script>
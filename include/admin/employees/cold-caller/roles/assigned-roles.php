<?php
global $wpdb;

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}cc_role_relation CRR

    left join {$wpdb->prefix}cc_role_meta CRM
    on CRR.role_id = CRM.id

    left join {$wpdb->prefix}cold_caller_types CCT
    on CRM.role_id = CCT.id

    left join {$wpdb->prefix}branches B
    on CRM.branch_id = B.id

    left join {$wpdb->prefix}employees E
    on CRR.cold_caller_id = E.id

");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$assigned_roles = $wpdb->get_results("
    select CRR.*, B.location_name, E.name as cold_caller_name, CCT.name as role_name
    from {$wpdb->prefix}cc_role_relation CRR

    left join {$wpdb->prefix}cc_role_meta CRM
    on CRR.role_id = CRM.id

    left join {$wpdb->prefix}cold_caller_types CCT
    on CRM.role_id = CCT.id

    left join {$wpdb->prefix}branches B
    on CRM.branch_id = B.id

    left join {$wpdb->prefix}employees E
    on CRR.cold_caller_id = E.id

    LIMIT $offset, $no_of_records_per_page
");
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Linked Roles</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Assigned To</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($assigned_roles) && count($assigned_roles) > 0): ?>
                                <?php foreach($assigned_roles as $assigned_role): ?>
                                    <tr>
                                        <td><?= $assigned_role->location_name." ".$assigned_role->role_name; ?></td>
                                        <td><?= $assigned_role->cold_caller_name; ?></td>
                                        <td><?= date('d M Y h:i A', strtotime($assigned_role->created_at)); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="deleteRecord(<?= $assigned_role->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-unlink"></i></span> Unlink / Delete</a></li>
                                                </ul>
                                            </div>                                            
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>No Role assigned to any cold caller yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function deleteRecord(record_id, ref){
        if(!confirm('Are you sure, you want to unlink cold caller from this role? It will also delete this link record')) return false;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            dataType: "json",
            data:{
                action: "unassign_cold_caller_role",
                record_id,
                "_wpnonce": "<?= wp_create_nonce('unassign_cold_caller_role') ?>"
            },
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success: function(data){
                if(data.status === "success"){
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                }
            }
        })

    }
</script>
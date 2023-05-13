<?php

global $wpdb;

$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}office_role_employee ORE

    left join {$wpdb->prefix}roles R
    on R.id = ORE.role_id

    left join {$wpdb->prefix}employees E
    on E.id = ORE.employee_id
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$linked_employees_roles = $wpdb->get_results("
    select R.name as role_name, E.name as employee_name, ORE.id, ORE.employee_id
    from {$wpdb->prefix}office_role_employee ORE

    left join {$wpdb->prefix}roles R
    on R.id = ORE.role_id

    left join {$wpdb->prefix}employees E
    on E.id = ORE.employee_id

");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Linked Roles/Employees <small>(<?= $total_rows; ?> records found)</small></h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Employee</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($linked_employees_roles) && count($linked_employees_roles) > 0): ?>
                                <?php foreach($linked_employees_roles as $linked_employees_role): ?>
                                    <tr>
                                        <td><?= $linked_employees_role->role_name; ?></td>
                                        <td><?= $linked_employees_role->employee_name; ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                                    <li><a onclick="unlinkEmployee(<?= $linked_employees_role->id; ?>, this)" href="javascript:void(0)"><span><i class="fa fa-unlink"></i></span> Unlink Employee</a></li>
                                                </ul>
                                            </div>                                            
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
    function unlinkEmployee(record_id, ref){
        if(!confirm('Are you sure you want to unlink employee from this role ?')) return;

        jQuery.ajax({
            type: "post",
            url: "<?= admin_url('admin-ajax.php'); ?>",
            data: {
                action: "unlink_employee_from_role",
                record_id,
                "_wpnonce": "<?= wp_create_nonce('unlink_employee_from_role'); ?>"
            },
            dataType: "json",
            beforeSend: function(){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
            },
            success: function(data){
                jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                if(data.status == "success"){
                    jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                }
            }
        });
    }
</script>
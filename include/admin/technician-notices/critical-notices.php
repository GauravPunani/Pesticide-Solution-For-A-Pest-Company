<?php

global $wpdb;

$conditions=[];

$conditions[]=" TAS.level='critical'";

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" TD.branch_id IN ($accessible_branches)";
}

if(!empty($_GET['technician_id'])) $conditions[] = "TD.id = '{$_GET['technician_id']}'";

if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'technician_account_status');
    $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'TAS');
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';
$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var("
    select count(*) 
    from {$wpdb->prefix}technician_account_status TAS 
    left join {$wpdb->prefix}technician_details TD 
    on TD.id=TAS.technician_id 
    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$notices=$wpdb->get_results("
    select TAS.*,TD.first_name,TD.last_name 
    from {$wpdb->prefix}technician_account_status TAS 
    left join {$wpdb->prefix}technician_details TD 
    on TD.id=TAS.technician_id 
    $conditions
    order by TAS.date DESC
    LIMIT $offset, $no_of_records_per_page
");

$technicians = (new Technician_details)->get_all_technicians();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Filters</h3>
                    <form>
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                        <div class="form-group">
                            <label for="">Search</label>
                            <input type="text" class="form-control" name="search" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="">Technician</label>
                            <select name="technician_id" id="" class="form-control select2-field">
                                <option value="">All</option>
                                <?php if(is_array($technicians) && count($technicians) > 0): ?>
                                    <?php foreach($technicians as $technician): ?>
                                        <option value="<?= $technician->id; ?>"><?= $technician->first_name." ".$technician->last_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-9">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Type</th>
                                <th>Notice</th>
                                <th>Date</th>
                                <th>Week</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($notices) && count($notices)>0): ?>
                                <?php foreach($notices as $notice): ?>
                                    <tr>
                                        <td><?= $notice->first_name." ".$notice->last_name; ?></td>
                                        <td><?= (new GamFunctions)->beautify_string($notice->type); ?></td>
                                        <td><?= $notice->notice; ?> </td>
                                        <td><?= date('d M Y',strtotime($notice->date)); ?> </td>
                                        <td><?= $notice->week; ?></td>
                                        <td><button onclick="deleteNotice('<?= $notice->id; ?>',this)" class="btn btn-danger"><span><i class="fa fa-trash"></i></span></button></td>
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
    (function($){
        $(document).ready(function(){

        })
    })(jQuery);

    function deleteNotice(notice_id,ref){

        if(confirm('Are you sure you want to delete this notice?')){
            // call ajax to delete the notice
            jQuery.ajax({
                type:"post",
                url:"<?= admin_url('admin-ajax.php'); ?>",
                data:{
                    action:"delete_critical_notice",
                    notice_id:notice_id,
                    "_wpnonce":"<?= wp_create_nonce('delete_critical_notice'); ?>"
                },
                beforeSend:function(){

                },
                dataType:"json",
                success:function(data){
                    if(data.status=="success"){
                        jQuery(ref).parent().parent().fadeOut();
                    }
                    else{
                        alert('Something went wrong, please try again later');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert('Something went wrong, please try again later');
                    console.log(xhr.responseText);
                }
            })
        }

    }
</script>
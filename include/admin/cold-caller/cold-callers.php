<?php
global $wpdb;

if(!empty($_GET['application_id'])){
    $cold_caller_id = sanitize_text_field($_GET['application_id']);
    return get_template_part('/include/admin/cold-caller/verify-application', null, ['id' => $cold_caller_id]);
}

if(!empty($_GET['cold_caller_id'])){
    return get_template_part("/include/admin/cold-caller/view-profile", null,['cold_caller_id' => $_GET['cold_caller_id']]);
}

if(!empty($_GET['cold-caller-edit-id'])){
    return get_template_part("template-parts/cold-caller/edit-profile", null, ['cold_caller_id' => $_GET['cold-caller-edit-id']]);
}

if(!empty($_GET['cold-caller-edit-password'])){
    return get_template_part("/include/admin/cold-caller/edit-cold-caller-password",null,['password'=>$_GET['cold-caller-edit-password']]);
}

$branches= (new Branches)->getAllBranches(false);
$upload_dir = wp_upload_dir();
$conditions = [];

if(!empty($_GET['branch_id']) && $_GET['branch_id']!=="all"){
    $conditions[] = " C.branch_id='{$_GET['branch_id']}'"; 
}

if(!empty($_GET['status'])){
    $status = sanitize_text_field($_GET['status']);
    switch ($status) {
        case 'active':
            $conditions[] = " C.status = 'active' and C.application_status = 'verified' ";
        break;
        case 'inactive':
            $conditions[] = " C.status = 'inactive' and C.application_status = 'verified' ";
        break;
        case 'fired':
            $conditions[] = " C.application_status = 'fired' ";
        break;
        case 'pending':
            $conditions[] = " C.application_status = 'pending' ";
        break;
    }
}else{
    $conditions[] = " C.status = 'active' and C.application_status = 'verified' ";
}


if(!empty($_GET['search'])){
    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'cold_callers');
    $conditions[] =(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'no_type', 'C');
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : "";

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_rows = $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}cold_callers C

    left join {$wpdb->prefix}cold_caller_types CT
    on C.type_id = CT.id

    left join {$wpdb->prefix}branches B
    on B.id = C.branch_id

    $conditions
");

$total_pages = ceil($total_rows / $no_of_records_per_page);

$cold_callers = $wpdb->get_results("
    select C.*, CT.name as type, B.location_name 
    from {$wpdb->prefix}cold_callers C

    left join {$wpdb->prefix}cold_caller_types CT
    on C.type_id = CT.id

    left join {$wpdb->prefix}branches B
    on B.id = C.branch_id

    $conditions
    order by created_at DESC
    LIMIT $offset, $no_of_records_per_page 
");

?>

<div class="container-fluid">
    <div class="row">

        <?php (new GamFunctions)->getFlashMessage(); ?>

        <!-- Branch Filter  -->
        <div class="col-sm-12 col-md-3">
            <div class="card">
                <div class="card-body">                    
                    <h4 class="text-center"><b><span><i class="fa fa-filter"></i></span> Filters</b></h4>
                        <form action="<?= $_SERVER['REQUEST_URI']; ?>">

                            <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                            <div class="form-group">
                                <label for="">Search</label>
                                <input type="text" class="form-control" name="search" value="<?= !empty($_GET['search']) ? $_GET['search'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="">Branch</label>
                                <select name="branch_id" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <?php if(is_array($branches) && count($branches)>0): ?>
                                        <?php foreach($branches as $key=>$val): ?>
                                            <option value="<?= $val->id; ?>" <?= $val->id == @$_GET['branch_id'] ? 'selected': ''; ?>><?= $val->location_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <!--<option value="all">All</option>-->
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">By Status</label>
                                <select name="status" class="form-group select2-field">
                                    <option value="">All</option>
                                    <option value="active" <?= (!empty($_GET['status']) && $_GET['status'] == "active") ? 'selected' : ''; ?>>Active Cold Callers</option>
                                    <option value="inactive" <?= (!empty($_GET['status']) && $_GET['status'] == "inactive") ? 'selected' : ''; ?>>Inactive Cold Callers</option>
                                    <option value="pending" <?= (!empty($_GET['status']) && $_GET['status'] == "pending") ? 'selected' : ''; ?>>Pending Verification</option>
                                    <option value="fired" <?= (!empty($_GET['status']) && $_GET['status'] == "fired") ? 'selected' : ''; ?>>Fired Cold Callers</option>
                                </select>
                            </div>

                            <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                        </form>
                </div>
            </div>
        </div>
        
        <!-- Cold Callers  -->
        <div class="col-sm-12 col-md-9">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Cold Callers</h3>
                    <p><?= $total_rows ?> Cold Callers Found</p>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
								<th>Branch</th>
								<th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($cold_callers) && count($cold_callers)>0): ?>
                                <?php foreach($cold_callers as $cold_caller): ?>
                                    <tr>
                                        <td><?= $cold_caller->name; ?></td>
                                        <td><?= $cold_caller->email; ?></td>
										<td><?= $cold_caller->location_name;?></td>
										<td>
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">

                                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&cold_caller_id=<?= $cold_caller->id; ?>"><span><i class="fa fa-eye"></i></span> View Profile</a></li>

                                                    <li><a target="_blank"  href="<?= $_SERVER['REQUEST_URI']; ?>&cold-caller-edit-password=<?= $cold_caller->id; ?>"><span><i class="fa fa-key"></i></span> Edit Password</a></li>

                                                    <li><a target="_blank"  href="<?= $_SERVER['REQUEST_URI']; ?>&cold-caller-edit-id=<?= $cold_caller->id; ?>"><span><i class="fa fa-edit"></i></span> Edit Cold Caller Profile</a></li>


                                                    <?php if($cold_caller->application_status == "verified"): ?>
                                                        <li><a onclick="fireColdCaller('<?= $cold_caller->id; ?>', this, 'fire')" href="javascript:void(0)"><span><i class="fa fa-ban"></i></span> Fire Cold Caller</a></li>
                                                    <?php elseif($cold_caller->application_status == "fired"): ?>
                                                        <li><a onclick="rehireColdCaller('<?= $cold_caller->id; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-plus"></i></span> Re-Hire Cold Caller</a></li>
                                                    <?php elseif($cold_caller->application_status == "pending"): ?>
                                                        <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&application_id=<?= $cold_caller->id; ?>"><span><i class="fa fa-check"></i></span> Verify Ac</a></li>
                                                        <li><a onclick="deleteAc('<?= $cold_caller->id; ?>', this)" href="javascript:void(0)"><span><i class="fa fa-trash"></i></span> Delete Ac</a></li>
                                                    <?php endif; ?>

                                                </ul>
                                            </div>
                                        </td>
                                    </tr>      
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No Cold Caller Found</td>
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

    function fireColdCaller(cold_caller_id, ref){

        if(confirm('Are you sure you want to fire this cold caller ?')){
            jQuery.ajax({
                type:"post",
                url:"<?= admin_url('admin-ajax.php'); ?>",
                dataType:"json",
                data:{
                    action:"fire_cold_caller",
                    cold_caller_id:cold_caller_id,
                    "_wpnonce": "<?= wp_create_nonce('fire_cold_caller'); ?>"                    
                },
                beforeSend:function(){
                    jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled',true);
                },
                success:function(data){
                    if(data.status=="success"){
                        alert(data.message);
                        jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                    }
                    else{
                        alert(data.message);
                        jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled',true);
                    }
                },
                error: function (request, status, error) {
                    alert('Something went wrong, please try again later');
                    jQuery(ref).attr('disabled',false);
                }
            });
        }
    }

    function rehireColdCaller(cold_caller_id, ref){

        if(confirm('Are you sure you want to re-hire this cold caller ?')){
            jQuery.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                dataType: "json",
                data:{
                    action:"rehire_cold_caller",
                    cold_caller_id:cold_caller_id,
                    "_wpnonce": "<?= wp_create_nonce('rehire_cold_caller'); ?>"
                },
                beforeSend:function(){
                    jQuery(ref).attr('disabled',true);
                },
                success:function(data){
                    if(data.status == "success"){
                        alert(data.message);
                        jQuery(ref).parent().parent().fadeOut();
                    }
                    else{
                        alert(data.message);
                        jQuery(ref).attr('disabled',false);
                    }
                },
                error: function (request, status, error) {
                    alert('Something went wrong, please try again later');
                    jQuery(ref).attr('disabled',false);
                }
            });
        }
    }

    function deleteAc(cold_caller_id, ref){
        if(confirm('Are you sure you want to delete cold caller account ? Deleting cold caller account will also delete all his/her leads and payment proof records as well from system')){
            jQuery.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                data: {
                    action: 'delete_cold_caller_ac',
                    cold_caller_id: cold_caller_id,
                    "_wpnonce": "<?= wp_create_nonce('delete_cold_caller_ac'); ?>"
                },
                dataType: "json",
                beforeSend: function(){
                    jQuery(ref).attr('disabled',true);
                },
                success: function(data){
                    if(data.status == "success"){
                        alert(data.message);
                        jQuery(ref).parent().parent().fadeOut();                        
                    }
                    else{
                        alert(data.message);
                        jQuery(ref).attr('disabled',false);
                    }
                },
                error: function(request, status, error){
                    alert('Something went wrong, please try again later');
                    jQuery(ref).attr('disabled',false);
                }
            })
        }
    }
</script>